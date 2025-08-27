<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;

class FormBrowseController extends Controller
{
    public function index(Request $r)
    {
        $q = Form::with('department')->where('is_active',true)->latest();
        if ($r->filled('department_id')) $q->where('department_id',$r->department_id);
        $forms = $q->get()->filter(fn($f) => $r->user()->can('view',$f));
        return view('front.forms.index', ['forms'=>$forms]);
    }

    public function show(Form $form)
    {
        $this->authorize('view',$form);
        return view('front.forms.show', compact('form'));
    }
}
