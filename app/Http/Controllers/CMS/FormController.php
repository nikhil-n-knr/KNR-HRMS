<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FormController extends Controller
{
    protected function tenantId(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $siteId = $request->get('site_id') ?? DB::table('cms_sites')->where('tenant_id', $this->tenantId())->value('id');
        return response()->json(
            DB::table('cms_forms')
                ->where('tenant_id', $this->tenantId())
                ->when($siteId, fn($q) => $q->where('site_id', $siteId))
                ->whereNull('deleted_at')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'slug'     => 'nullable|string|max:255',
            'fields'   => 'nullable|array',
            'settings' => 'nullable|array',
        ]);
        $data['tenant_id']          = $this->tenantId();
        $data['site_id']            = $request->get('site_id') ?? DB::table('cms_sites')->where('tenant_id', $this->tenantId())->value('id');
        $data['slug']               = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active']          = true;
        $data['submissions_count']  = 0;
        if (isset($data['fields']))   $data['fields']   = json_encode($data['fields']);
        if (isset($data['settings'])) $data['settings'] = json_encode($data['settings']);
        $data['created_at'] = $data['updated_at'] = now();
        $id = DB::table('cms_forms')->insertGetId($data);
        return response()->json(DB::table('cms_forms')->find($id), 201);
    }

    public function show(string $id)
    {
        $form = DB::table('cms_forms')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$form, 404);
        return response()->json($form);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->only(['name','slug','fields','settings','is_active']);
        foreach (['fields','settings'] as $col) {
            if (isset($data[$col]) && is_array($data[$col])) $data[$col] = json_encode($data[$col]);
        }
        $data['updated_at'] = now();
        DB::table('cms_forms')->where('tenant_id', $this->tenantId())->where('id', $id)->update($data);
        return response()->json(DB::table('cms_forms')->find($id));
    }

    public function destroy(string $id)
    {
        DB::table('cms_forms')->where('tenant_id', $this->tenantId())->where('id', $id)
            ->update(['deleted_at' => now()]);
        return response()->json(['deleted' => true]);
    }

    /** Return all submissions for a form. */
    public function submissions(string $id)
    {
        $form = DB::table('cms_forms')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$form, 404);
        return response()->json(
            DB::table('cms_form_submissions')->where('form_id', $id)->orderByDesc('created_at')->paginate(50)
        );
    }

    /**
     * Public form submission (no auth middleware).
     * Creates CRM lead if settings.crm_lead_map is configured.
     */
    public function submit(Request $request, string $id)
    {
        $form = DB::table('cms_forms')->where('id', $id)->where('is_active', true)->first();
        abort_if(!$form, 404, 'Form not found or inactive.');

        $submittedData = $request->except(['_token', '_method']);
        $settings      = json_decode($form->settings ?? '{}', true);

        // Save submission
        $submissionId = DB::table('cms_form_submissions')->insertGetId([
            'form_id'    => $form->id,
            'site_id'    => $form->site_id,
            'data'       => json_encode($submittedData),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer'   => $request->header('Referer'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Increment form counter
        DB::table('cms_forms')->where('id', $id)->increment('submissions_count');

        // Auto-create CRM lead if mapping configured
        $leadId = null;
        if (!empty($settings['crm_lead_map'])) {
            $map = $settings['crm_lead_map'];
            $leadId = DB::table('crm_leads')->insertGetId([
                'tenant_id'  => $form->tenant_id,
                'first_name' => $submittedData[$map['name'] ?? 'name'] ?? 'Unknown',
                'email'      => $submittedData[$map['email'] ?? 'email'] ?? null,
                'phone'      => $submittedData[$map['phone'] ?? 'phone'] ?? null,
                'source'     => 'cms_form',
                'status'     => 'new',
                'notes'      => 'Form: ' . $form->name . "\n" . json_encode($submittedData),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('cms_form_submissions')->where('id', $submissionId)->update(['crm_lead_id' => $leadId]);
        }

        // Redirect if configured
        if (!empty($settings['redirect_url'])) {
            return redirect($settings['redirect_url']);
        }

        return response()->json([
            'success'    => true,
            'message'    => $settings['success_message'] ?? 'Thank you! Your message has been received.',
            'crm_lead_id' => $leadId,
        ]);
    }

    /** Delete a single submission. */
    public function destroySubmission(string $id)
    {
        $sub = DB::table('cms_form_submissions')->where('id', $id)->first();
        abort_if(!$sub, 404);
        
        DB::table('cms_form_submissions')->where('id', $id)->delete();
        DB::table('cms_forms')->where('id', $sub->form_id)->decrement('submissions_count');
        
        return response()->json(['deleted' => true]);
    }
}
