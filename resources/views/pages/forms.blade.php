@extends('index')

@section('additional-css')
    <style type="text/css">
        .column {
            max-width: 450px;
        }
    </style>
@endsection

@section('main-container')
    @include('pages.modules.default.main-menu')
    <div class="ui middle aligned center aligned grid" style="margin-top: 100px">
        <div class="column">
            <h2>@yield('header-text')</h2>
            <form class="ui form" id="@yield('form-id')">
                <div class="ui segment">
                    @yield('inputs')
                    @include('pages.modules.default.recaptcha_standalone')
                    <div class="field">
                        <button class="ui fluid alpha button" type="submit">@yield('submit-text')</button>
                    </div>
                    @yield('after-links')
                </div>
            </form>
        </div>
    </div>
@endsection
