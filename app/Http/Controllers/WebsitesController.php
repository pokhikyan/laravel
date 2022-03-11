<?php

namespace App\Http\Controllers;

use App\Models\Websites;
use Illuminate\Http\Request;

class WebsitesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $websites = Websites::orderBy('company','asc')->paginate(50);

        return view('websites.index', compact('websites'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('websites.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
                               'company' => 'required',
                               'url' => 'required',
                               'active' => 'required'
                           ]);

        Websites::create($request->all());

        return redirect()->route('websites.index')
            ->with('success', 'Website created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Websites  $websites
     * @return \Illuminate\Http\Response
     */
    public function show(Websites $websites)
    {
        return view('websites.show', compact('websites'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Websites  $websites
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $website = Websites::where('id',$id)->first();
        return view('websites.edit', compact('website'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Websites  $websites
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Websites $websites)
    {
        $request->validate([
                               'company' => 'required',
                               'url' => 'required',
                           ]);
        Websites::where(['id'=>$request->id])->update(['company'=>$request->company, 'url'=>$request->url, 'active'=>$request->has('active')]);
        return redirect()->route('company.edit',['id' => $request->id])
            ->with('success', 'Website updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Websites  $websites
     * @return \Illuminate\Http\Response
     */
    public function destroy(Websites $websites)
    {
        $websites->delete();

        return redirect()->route('websites.index')
            ->with('success', 'Website deleted successfully');
    }
}
