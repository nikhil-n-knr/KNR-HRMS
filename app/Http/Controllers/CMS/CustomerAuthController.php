<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CustomerAuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $site = $this->resolveSite($request);
        return Inertia::render('CMS/Public/Auth/Login', [
            'site' => $site,
            'theme' => $this->getTheme($site),
        ]);
    }

    public function showRegister(Request $request)
    {
        $site = $this->resolveSite($request);
        return Inertia::render('CMS/Public/Auth/Register', [
            'site' => $site,
            'theme' => $this->getTheme($site),
        ]);
    }

    public function login(Request $request)
    {
        $site = $this->resolveSite($request);
        
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $customer = Customer::where('site_id', $site->id)
            ->where('email', $request->email)
            ->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials for this store.'],
            ]);
        }

        if (!$customer->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive. Please contact support.'],
            ]);
        }

        Auth::guard('customer')->login($customer, $request->boolean('remember'));
        
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function register(Request $request)
    {
        $site = $this->resolveSite($request);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:cms_customers,email,NULL,id,site_id,' . $site->id,
            'password' => 'required|string|min:8|confirmed',
        ]);

        $customer = Customer::create([
            'site_id' => $site->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        Auth::guard('customer')->login($customer);

        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // Helper methods to match PublicSiteController patterns
    protected function resolveSite(Request $request)
    {
        if ($slug = $request->query('preview_site')) {
            $site = DB::table('cms_sites')->where('slug', $slug)->first();
            if ($site) return $site;
        }
        $site = DB::table('cms_sites')->where('domain', $request->getHost())->where('status', 'live')->first();
        if (!$site) $site = DB::table('cms_sites')->where('status', 'live')->first();
        if (!$site) $site = DB::table('cms_sites')->first();
        return $site;
    }

    protected function getTheme($site)
    {
        $theme = $site && $site->theme_id ? DB::table('cms_themes')->where('id', $site->theme_id)->first() : null;
        return $theme ? json_decode($theme->config ?? '{}', true) : [
            'colors' => ['primary' => '#4f46e5', 'secondary' => '#6366f1'],
            'typography' => ['font_heading' => 'Inter', 'font_body' => 'Inter'],
        ];
    }
}
