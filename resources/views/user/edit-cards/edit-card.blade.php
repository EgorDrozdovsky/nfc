@extends('layouts.user', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

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
                        {{ __('Edit Business Card') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-sm-12 col-lg-12">
                    <form action="{{ route('user.update.business.card', Request::segment(3)) }}" method="post"
                        enctype="multipart/form-data" class="card">
                        @csrf
                        {{-- Create Card --}}
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5 col-xl-5">
                                    <div class="mb-3">
                                        <div class="row g-2">
                                            <div class="col-12 col-sm-12">
                                                <label class="form-imagecheck mb-2">
                                                    <input type="radio" name="theme_id" value="{{ $themes->theme_id }}"
                                                        class="form-imagecheck-input"
                                                        {{ $themes->theme_id == $business_card->theme_id ? 'checked' : '' }} />
                                                    <span class="form-imagecheck-figure">
                                                        <img src="{{ asset('backend/img/vCards/'.$themes->theme_thumbnail) }}"
                                                            class="w-100 h-100 object-cover"
                                                            alt="{{ $themes->theme_name }}"
                                                            class="form-imagecheck-image">
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7 col-xl-7">
                                    <div class="row">
                                        <div class="col-md-12 col-xl-12">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Card Color') }}</label>
                                                <div class="row g-2">

                                                    <div class="col-auto">
                                                        <label class="form-colorinput">
                                                            <input name="card_color" type="radio" value="blue"
                                                                class="form-colorinput-input"
                                                                {{ $business_card->theme_color == "blue" ? 'checked' : '' }} />
                                                            <span class="form-colorinput-color bg-blue"></span>
                                                        </label>
                                                    </div>
                                                    <div class="col-auto">
                                                        <label class="form-colorinput form-colorinput-light">
                                                            <input name="card_color" type="radio" value="indigo"
                                                                class="form-colorinput-input"
                                                                {{ $business_card->theme_color == "indigo" ? 'checked' : '' }} />
                                                            <span class="form-colorinput-color bg-indigo"></span>
                                                        </label>
                                                    </div>
                                                    <div class="col-auto">
                                                        <label class="form-colorinput">
                                                            <input name="card_color" type="radio" value="green"
                                                                class="form-colorinput-input"
                                                                {{ $business_card->theme_color == "green" ? 'checked' : '' }} />
                                                            <span class="form-colorinput-color bg-green"></span>
                                                        </label>
                                                    </div>
                                                    <div class="col-auto">
                                                        <label class="form-colorinput">
                                                            <input name="card_color" type="radio" value="yellow"
                                                                class="form-colorinput-input"
                                                                {{ $business_card->theme_color == "yellow" ? 'checked' : '' }} />
                                                            <span class="form-colorinput-color bg-yellow"></span>
                                                        </label>
                                                    </div>

                                                    <div class="col-auto">
                                                        <label class="form-colorinput">
                                                            <input name="card_color" type="radio" value="red"
                                                                class="form-colorinput-input"
                                                                {{ $business_card->theme_color == "red" ? 'checked' : '' }} />
                                                            <span class="form-colorinput-color bg-red"></span>
                                                        </label>
                                                    </div>
                                                    <div class="col-auto">
                                                        <label class="form-colorinput">
                                                            <input name="card_color" type="radio" value="purple"
                                                                class="form-colorinput-input"
                                                                {{ $business_card->theme_color == "purple" ? 'checked' : '' }} />
                                                            <span class="form-colorinput-color bg-purple"></span>
                                                        </label>
                                                    </div>
                                                    <div class="col-auto">
                                                        <label class="form-colorinput">
                                                            <input name="card_color" type="radio" value="pink"
                                                                class="form-colorinput-input"
                                                                {{ $business_card->theme_color == "pink" ? 'checked' : '' }} />
                                                            <span class="form-colorinput-color bg-pink"></span>
                                                        </label>
                                                    </div>
                                                    <div class="col-auto">
                                                        <label class="form-colorinput form-colorinput-light">
                                                            <input name="card_color" type="radio" value="gray"
                                                                class="form-colorinput-input"
                                                                {{ $business_card->theme_color == "gray" ? 'checked' : '' }} />
                                                            <span class="form-colorinput-color bg-muted"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <div class="form-label required">{{ __('Cover') }} <span
                                                        class="text-danger">({{ __('Recommended : 576 x 160 pixels') }})</span>
                                                </div>
                                                <input type="file" class="form-control" name="cover"
                                                    placeholder="{{ __('Cover') }}..."
                                                    value="{{ $business_card->cover }}"
                                                    accept=".jpeg,.jpg,.png,.gif,.svg" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <div class="form-label required">{{ __('Logo') }} <span
                                                        class="text-danger">({{ __('Recommended : 128 x 128 pixels') }})</span>
                                                </div>
                                                <input type="file" class="form-control" name="logo"
                                                    placeholder="{{ __('Logo') }}..." value="{{ $business_card->logo }}"
                                                    accept=".jpeg,.jpg,.png,.gif,.svg" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Title') }}</label>
                                                <input type="text" class="form-control" name="title"
                                                    placeholder="{{ __('Business name / Your name') }}..."
                                                    value="{{ $business_card->title }}" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Sub Title') }}</label>
                                                <input type="text" class="form-control" name="subtitle"
                                                    placeholder="{{ __('Location / Job title') }}..."
                                                    value="{{ $business_card->sub_title }}" required>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-xl-12">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Description') }}</label>
                                                <textarea class="form-control" name="description"
                                                    data-bs-toggle="autosize"
                                                    placeholder="{{ __('About business / Bio') }}..."
                                                    required>{{ $business_card->description }}</textarea>
                                            </div>
                                        </div>

                                        @if ($plan_details->personalized_link)
                                        <div class="col-md-10 col-xl-10">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Personalized Link') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        {{ URL::to('/') }}
                                                    </span>
                                                    <input type="text" class="form-control" name="link"
                                                        placeholder="{{ __('Personalized Link') }}" autocomplete="off"
                                                        id="plink" onkeyup="checkLink()" minlength="3"
                                                        value="{{ $business_card->card_url }}" required>
                                                </div>
                                                <p id="status"></p>
                                            </div>
                                        </div>
                                        @endif

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
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('user.includes.footer')
</div>

@push('custom-js')
<script>
    function checkLink(){
    "use strict";
    var plink = $('#plink').val();
    if(plink.length > 2){

    $.ajax({
    url: "{{ route('user.check.link') }}",
    method: 'POST',
    data:{_token: "{{ csrf_token() }}", link: plink},
    }).done(function(res) {
        if(res.status == 'success') {
            $('#status').html("<span class='badge mt-2 bg-green'>{{ __('Available') }}</span>");
        }else{
            $('#status').html("<span class='badge mt-2 bg-red'>{{ __('Not available') }}</span>");
        }
    });
}else{
    $('#status').html("");
}
}
</script>
@endpush
@endsection