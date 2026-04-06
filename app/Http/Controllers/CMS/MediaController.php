<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    protected function tenantId(): int { return auth()->user()->tenant_id; }
    protected function siteId(Request $request): ?int {
        return $request->get('site_id') ?? DB::table('cms_sites')->where('tenant_id', $this->tenantId())->value('id');
    }

    public function index(Request $request)
    {
        return response()->json(
            DB::table('cms_media')
                ->where('tenant_id', $this->tenantId())
                ->when($this->siteId($request), fn($q) => $q->where('site_id', $this->siteId($request)))
                ->when($request->type, fn($q) => $q->where('file_type', $request->type))
                ->orderByDesc('created_at')
                ->paginate(40)
        );
    }

    public function store(Request $request)
    {
        $request->validate(['file' => 'required|file|max:51200']); // 50MB max
        $file     = $request->file('file');
        $mime     = $file->getMimeType();
        $fileType = str_starts_with($mime, 'image/') ? 'image' :
                   (str_starts_with($mime, 'video/') ? 'video' :
                   (str_starts_with($mime, 'audio/') ? 'audio' : 'document'));

        $folder   = 'cms/media/' . $this->tenantId();
        $path     = $file->store($folder, 'public');
        $url      = Storage::url($path);
        $siteId   = $this->siteId($request);

        $id = DB::table('cms_media')->insertGetId([
            'tenant_id'   => $this->tenantId(),
            'site_id'     => $siteId,
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $path,
            'url'         => $url,
            'file_type'   => $fileType,
            'mime_type'   => $mime,
            'file_size'   => $file->getSize(),
            'alt'         => $request->get('alt', Str::title(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))),
            'caption'     => $request->get('caption'),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(DB::table('cms_media')->find($id), 201);
    }

    public function show(string $id)
    {
        $media = DB::table('cms_media')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$media, 404);
        return response()->json($media);
    }

    public function update(Request $request, string $id)
    {
        $data = array_merge($request->only(['alt', 'caption', 'tags']), ['updated_at' => now()]);
        DB::table('cms_media')->where('tenant_id', $this->tenantId())->where('id', $id)->update($data);
        return response()->json(DB::table('cms_media')->find($id));
    }

    public function destroy(string $id)
    {
        $media = DB::table('cms_media')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$media, 404);
        Storage::disk('public')->delete($media->file_path);
        DB::table('cms_media')->where('id', $id)->delete();
        return response()->json(['deleted' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array'])['ids'];
        $items = DB::table('cms_media')->where('tenant_id', $this->tenantId())->whereIn('id', $ids)->get();
        foreach ($items as $m) { Storage::disk('public')->delete($m->file_path); }
        DB::table('cms_media')->where('tenant_id', $this->tenantId())->whereIn('id', $ids)->delete();
        return response()->json(['deleted' => count($items)]);
    }
}
