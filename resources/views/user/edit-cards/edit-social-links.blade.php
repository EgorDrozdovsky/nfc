@extends('layouts.user', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('css')
<link rel="stylesheet" href="{{ asset('css/all.css') }}" />
<link rel="stylesheet" href="{{ asset('css/bootstrap-iconpicker.min.css') }}" />
@endsection
@section('content')
<div class="page-wrapper">
    <div class="container-xl">
        <!-- Page title -->
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        {{ __('Overview') }}
                    </div>
                    <h2 class="page-title">
                        {{ __('Card Features') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-sm-12 col-lg-12">
                    <form action="{{ route('user.update.social.links', Request::segment(3)) }}" method="post"
                        class="card">
                        @csrf
                        <div class="card-body">
                            <div class="row">

                                <div class="col-lg-12">

                                    <div class="row">

                                        @for ($i = 0; $i < count($features); $i++) <div class='row' id="{{ $i }}">
                                            <div class='col-lg-1 col-md-2'>
                                                <div class='mb-3 mt-2'>
                                                    <label class='form-label required'>{{ __('Icon') }}</label>
                                                    <div class='input-group'>
                                                        <input type='text' class='form-control'
                                                            placeholder='{{ __('Choose Icon') }}' id='iconpick{{ $i }}'
                                                            onclick='openPicker({{ $i }})' name='icon[]'
                                                            value="{{ $features[$i]->icon }}" readonly required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col-lg-2 col-md-2'>
                                                <div class='mb-3 mt-2'>
                                                    <label class='form-label required'
                                                        for='type'>{{ __('Display type') }}</label>
                                                    <select name='type[]' id='type'
                                                        class='type{{ $features[$i]->id }} form-control'
                                                        onchange='changeLabel({{ $features[$i]->id }})' required>
                                                        <option value='' disabled selected>
                                                            {{ __('Choose Type') }}
                                                        </option>
                                                        <option value='text'
                                                            {{ $features[$i]->type == 'text' || $features[$i]->type == 'textarea' ? 'selected' : '' }}>
                                                            {{ __('Default') }}</option>
                                                        <option value='address'
                                                            {{ $features[$i]->type == 'address' ? 'selected' : '' }}>
                                                            {{ __('Address') }}</option>
                                                        <option value='email'
                                                            {{ $features[$i]->type == 'email' ? 'selected' : '' }}>
                                                            {{ __('Email') }}</option>
                                                        <option value='tel'
                                                            {{ $features[$i]->type == 'tel' ? 'selected' : '' }}>
                                                            {{ __('Phone') }}</option>
                                                        <option value='wa'
                                                            {{ $features[$i]->type == 'wa' ? 'selected' : '' }}>
                                                            {{ __('WhatsApp') }}</option>
                                                        <option value='url'
                                                            {{ $features[$i]->type == 'url' ? 'selected' : '' }}>
                                                            {{ __('Link') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class='col-lg-3 col-md-3'>
                                                <div class='mb-3 mt-2'>
                                                    <label class='form-label required'>{{ __('Label') }}</label>
                                                    <input type='text' class='lbl{{ $features[$i]->id }} form-control' name='label[]'
                                                        placeholder='{{ __('Label') }}...'
                                                        value="{{ $features[$i]->label }}" required>
                                                </div>
                                            </div>
                                            <div class='col-lg-4 col-md-4'>
                                                <div class='mb-3 mt-2'>
                                                    <label class='form-label required'>{{ __('Content') }}</label>
                                                    <input type="text" class='textlbl{{ $features[$i]->id }} form-control' name='value[]'
                                                        placeholder='{{ __('Value') }}...' value="{{ $features[$i]->content }}"
                                                        required>
                                                </div>
                                            </div>
                                            <div class='col-lg-1 col-md-1'>
                                                <div class='mb-3 pt-1 mt-4'>
                                                    <button class='btn btn-transparent'
                                                        onclick='removeFeature({{ $i }})'>
                                                        <i class='fa fa-times text-danger'></i>
                                                    </button>
                                                </div>
                                            </div>
                                    </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div id="more-features" class="row"></div>
                        <div class="col-lg-12">
                            <button type="button" onclick="addFeature()" class="btn btn-primary">
                                {{ __('Add One More Features') }}
                            </button>
                        </div>

                        <div class="col-md-4 col-xl-4 my-3">
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Submit & Next') }}
                                </button>
                            </div>
                        </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
@include('user.includes.footer')
</div>

@push('custom-js')
<script type="text/javascript" src="{{ asset('backend/js/fontawesome-iconpicker.min.js') }}"></script>
<script>
    var count = {{ count($features) }};
    function addFeature() {
        "use strict";
        if (count >= {{ $plan_details->no_of_features }}) {
            swal({
                title: 'Oops!',
                icon: 'warning',
                text: 'You have reached your current plan limit.',
                timer: 2000,
                buttons: false,
            });
        } else {
            count++;
            var id = getRandomInt();
            var features = "<div class='row' id=" + id +
                "><div class='col-lg-1 col-md-2'><div class='mb-3 mt-2'><label class='form-label required'>{{ __('Icon') }}</label><div class='input-group'><input type='text' class='form-control' placeholder='{{ __('Choose Icon') }}' id='iconpick" +
                id + "' onclick='openPicker(" + id +
                ")' name='icon[]' required readonly></div></div></div><div class='col-lg-2 col-md-2'><div class='mb-3 mt-2'><label class='form-label required' for='type'>{{ __('Display type') }}</label><select name='type[]' id='type' class='type"+ id +" form-control' onchange='changeLabel("+ id +")' required> <option value='' disabled selected>{{ __('Choose Type') }}</option><option value='text'>{{ __('Default') }}</option><option value='address'>{{ __('Address') }}</option><option value='email'>{{ __('Email') }}</option><option value='tel'>{{ __('Phone') }}</option><option value='wa'>{{ __('WhatsApp') }}</option><option value='url'>{{ __('Link') }}</option></select></div></div><div class='col-lg-3 col-md-3'><div class='mb-3 mt-2'><label class='form-label required'>{{ __('Label') }}</label><input type='text' class='lbl"+ id +" form-control' name='label[]' placeholder='{{ __('Label') }}...' required></div></div><div class='col-lg-4 col-md-4'><div class='mb-3 mt-2'><label class='form-label required'>{{ __('Content') }}</label><input type='text' class='textlbl"+ id +" form-control' name='value[]' placeholder='{{ __('Type something') }}...' required></div></div><div class='col-lg-1 col-md-1'> <div class='mb-2 pt-1 mt-4'><button class='btn btn-transparent' onclick='removeFeature(" +
                id + ")'><i class='fa fa-times text-danger'></i></button></div></div></div>";
            $("#more-features").append(features).html();
        }
    }

    function removeFeature(id) {
        "use strict";
        $("#" + id).remove();
        count--;
    }

    function getRandomInt() {
        min = Math.ceil(0);
        max = Math.floor(9999999999);
        return Math.floor(Math.random() * (max - min) + min); //The maximum is exclusive and the minimum is inclusive
    }

    function openPicker(id) {
        "use strict";
        $("#iconpick" + id).iconpicker({
            animation: true,
            hideOnSelect: true,
            placement: "inline",
            templates: {
                popover: '<div class="iconpicker-popover popover position-absolute"><div class="arrow"></div>' +
                    '<div class="popover-title"></div><div class="popover-content"></div></div>',
                iconpickerItem: '<a role="button" class="iconpicker-item"><i></i></a>'
            }
        });
    }

    function changeLabel(id) {
        "use strict";
        var label = 'Label';
        var textlabel = 'Type Something...';
        let lbl = document.querySelector('.lbl'+id);
        let textlbl = document.querySelector('.textlbl'+id);
        let type = document.querySelector('.type'+id).value;
        console.log(type);
        if(type == 'address') {
            label = "Address";
            textlabel = "For ex: Chennai, Tamilnadu, India";
        } else if(type == 'text') {
            label = "About us";
            textlabel = "For ex: GoBiz is a Digital Business Card Maker...";
        } else if(type == 'email') {
            label = "Email Address";
            textlabel = "For ex: support@gobiz.com";
        } else if(type == 'tel') {
            label = "Phone Number";
            textlabel = "For ex: +919876543210";
        } else if(type == 'wa') {
            label = "WhatsApp";
            textlabel = "For ex: 919876543210";
        } else if(type == 'url') {
            label = "Website";
            textlabel = "For ex: gobiz.com";
        }

        lbl.placeholder = label;
        textlbl.placeholder = textlabel;
    }
</script>
@endpush
@endsection
