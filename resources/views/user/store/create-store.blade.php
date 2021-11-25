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
                        {{ __('Create New WhatsApp Store') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-sm-12 col-lg-12">
                    <form action="{{ route('user.save.store') }}" method="post" enctype="multipart/form-data"
                        class="card">
                        @csrf
                        {{-- Create Card --}}
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5 col-xl-5">
                                    <div class="mb-3">
                                        <div class="row g-2">
                                            <div class="col-12 col-sm-12">
                                                <label class="form-imagecheck mb-2">
                                                    <input type="radio" name="theme_id"
                                                        value="{{ $themes->theme_id }}" class="form-imagecheck-input" required checked />
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
                                                                class="form-colorinput-input" required checked />
                                                            <span class="form-colorinput-color bg-blue"></span>
                                                        </label>
                                                    </div>
                                                    <div class="col-auto">
                                                        <label class="form-colorinput form-colorinput-light">
                                                            <input name="card_color" type="radio" value="indigo"
                                                                class="form-colorinput-input" required />
                                                            <span class="form-colorinput-color bg-indigo"></span>
                                                        </label>
                                                    </div>
                                                    <div class="col-auto">
                                                        <label class="form-colorinput">
                                                            <input name="card_color" type="radio" value="green"
                                                                class="form-colorinput-input" required />
                                                            <span class="form-colorinput-color bg-green"></span>
                                                        </label>
                                                    </div>

                                                    <div class="col-auto">
                                                        <label class="form-colorinput">
                                                            <input name="card_color" type="radio" value="red"
                                                                class="form-colorinput-input" required />
                                                            <span class="form-colorinput-color bg-red"></span>
                                                        </label>
                                                    </div>
                                                    <div class="col-auto">
                                                        <label class="form-colorinput">
                                                            <input name="card_color" type="radio" value="purple"
                                                                class="form-colorinput-input" required />
                                                            <span class="form-colorinput-color bg-purple"></span>
                                                        </label>
                                                    </div>
                                                    <div class="col-auto">
                                                        <label class="form-colorinput form-colorinput-light">
                                                            <input name="card_color" type="radio" value="gray"
                                                                class="form-colorinput-input" required />
                                                            <span class="form-colorinput-color bg-muted"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <div class="form-label required">{{ __('Banner') }} <span class="text-danger">({{ __('Recommended : 1280 x 426 pixels') }})</span></div>
                                                <input type="file" class="form-control" name="banner"
                                                    placeholder="{{ __('Banner') }}..." required accept=".jpeg,.jpg,.png,.gif,.svg" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <div class="form-label required">{{ __('Logo') }} <span class="text-danger">({{ __('Recommended: 200 x 80 pixels') }})</span></div>
                                                <input type="file" class="form-control" name="logo"
                                                    placeholder="{{ __('Logo') }}..." required accept=".jpeg,.jpg,.png,.gif,.svg" />
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Store name') }}</label>
                                                <input type="text" class="form-control" name="title"
                                                    placeholder="{{ __('Store name') }}..." required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Store greeting') }}</label>
                                                <input type="text" class="form-control" name="subtitle"
                                                    placeholder="{{ __('Ex: Welcome to') }}..." required>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <label class="form-label required" for="currency">{{ __('Currency') }}</label>
                                                <select name="currency" id="currency" class="form-control" required>
                                                    @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->symbol }}">
                                                        {{ $currency->name }} ({{ $currency->symbol }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('WhatsApp Number') }}</label>
                                                <input type="number" class="form-control" name="whatsapp_no"
                                                    placeholder="{{ __('For example: 919876543210 (With country code)') }}..." required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('WhatsApp Footer Text') }}</label>
                                                <textarea class="form-control" name="whatsapp_msg"
                                                    data-bs-toggle="autosize" placeholder="{{ __('WhatsApp Footer Text') }}..."
                                                    required>{{ __('Thanks for shopping with us.') }}</textarea>
                                            </div>
                                        </div>

                                        @if ($plan_details->personalized_link)
                                        <div class="col-md-10 col-xl-10 mt-3">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Personalized Link') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        {{ URL::to('/') }}
                                                    </span>
                                                    <input type="text" class="form-control" name="link"
                                                        placeholder="{{ __('Personalized Link') }}" autocomplete="off"
                                                        id="plink" onkeyup="checkLink()" minlength="3" required>
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
