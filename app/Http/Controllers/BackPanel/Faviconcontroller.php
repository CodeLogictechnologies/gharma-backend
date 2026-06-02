<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Favicon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaviconController extends Controller
{
    public function index()
    {
        $favicon = Favicon::first();
        return view('backend.favicon.index', compact('favicon'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'favicon' => 'required|mimes:png,ico,jpg,jpeg,gif,svg',
        ], [
            'favicon.required' => 'Please select a favicon image.',
            'favicon.mimes'    => 'Favicon must be a PNG, ICO, JPG, GIF, or SVG file.',
        ]);

        $favicon = Favicon::firstOrNew();

        if ($request->hasFile('favicon')) {
            // Delete old favicon if exists
            if (!empty($favicon->image) && Storage::disk('public')->exists('favicon/' . $favicon->image)) {
                Storage::disk('public')->delete('favicon/' . $favicon->image);
            }

            $file     = $request->file('favicon');
            $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('favicon', $filename, 'public');
            $favicon->image = $filename;
        }

        $favicon->save();

        return redirect()->back()->with('success', 'Favicon updated successfully.');
    }
}