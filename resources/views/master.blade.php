<!DOCTYPE html>

<html lang="en">
<head>
    <title>Vicinity Media Ad Manager</title>
    <meta charset="utf-8">

    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <!-- Favicon related -->
	<link rel="icon" type="image/png" sizes="192x192" href="{!!asset('favicon/android-icon-192x192.png')!!}">
	<link rel="icon" type="image/png" sizes="32x32" href="{!!asset('favicon/favicon-32x32.png')!!}">
	<link rel="icon" type="image/png" sizes="96x96" href="{!!asset('favicon/favicon-96x96.png')!!}">
	<link rel="icon" type="image/png" sizes="16x16" href="{!!asset('favicon/favicon-16x16.png')!!}">
	<link rel="manifest" href="{!!asset('favicon/manifest.json')!!}">
    <link rel="apple-touch-icon" sizes="57x57" href="{!!asset('favicon/apple-icon-57x57.png')!!}">
	<link rel="apple-touch-icon" sizes="60x60" href="{!!asset('favicon/apple-icon-60x60.png')!!}">
	<link rel="apple-touch-icon" sizes="72x72" href="{!!asset('favicon/apple-icon-72x72.png')!!}">
	<link rel="apple-touch-icon" sizes="76x76" href="{!!asset('favicon/apple-icon-76x76.png')!!}">
	<link rel="apple-touch-icon" sizes="114x114" href="{!!asset('favicon/apple-icon-114x114.png')!!}">
	<link rel="apple-touch-icon" sizes="120x120" href="{!!asset('faF/jvicon/apple-icon-120x120.png')!!}">
	<link rel="apple-touch-icon" sizes="144x144" href="{!!asset('favicon/apple-icon-144x144.png')!!}">
	<link rel="apple-touch-icon" sizes="152x152" href="{!!asset('favicon/apple-icon-152x152.png')!!}">
	<link rel="apple-touch-icon" sizes="180x180" href="{!!asset('favicon/apple-icon-180x180.png')!!}">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="{!!asset('favicon/ms-icon-144x144.png')!!}">
	<meta name="theme-color" content="#ffffff">
	<!-- Favicon related - end -->

    <!-- <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Roboto" /> -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    @yield('css')
    <link rel="stylesheet" type="text/css" href="{!!asset('bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css')!!}">
	<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="{!!asset('assets/css/bootstrap.min.css')!!}?v=2">
    <link rel="stylesheet" type="text/css" href="{!!asset('css/style.css')!!}">
    <link rel="stylesheet" type="text/css" href="{!!asset('css/styleOverride.css')!!}?v=1.2">
    <link rel="stylesheet" type="text/css" href="{!!asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css')!!}" />
    <link rel="stylesheet" type="text/css" href="{!!asset('assets/plugins/global/plugins.bundle.css')!!}" />
    <link rel="stylesheet" type="text/css" href="{!!asset('assets/css/style.bundle.css')!!}?v=2" />
    <link rel="stylesheet" type="text/css" href="{!!asset('css/plugins/Datatable.tableTool.css')!!}">
    <link rel="stylesheet" type="text/css" href="{!!asset('boostrap-switcher/css/bootstrap-switch.min.css')!!}">
    <link rel="stylesheet" type="text/css" href="{!!asset('css/plugins/metisMenu/metisMenu.min.css')!!}">
    <link rel="stylesheet" type="text/css" href="{!!asset('css/plugins/timeline.css')!!}">
    <link rel="stylesheet" type="text/css" href="{!!asset('Google-Web-Font-Selector/jquery.fontpicker.min.css')!!}">
    <link rel="stylesheet" type="text/css" href="{!!asset('css/jquery.dataTables.yadcf.0.9.2.css')!!}">
    <link rel="stylesheet" type="text/css" href="{!!asset('css/plugins/morris.css')!!}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/fixedheader/3.1.2/css/fixedHeader.dataTables.min.css"/>
	<link rel="stylesheet" type="text/css" href="/js/plugins/pivottable/dist/pivot.css">
	<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/css/bootstrap-select.min.css">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/vue-multi-select@3.11.1/dist/lib/vue-multi-select.min.css">
	<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/c3/0.4.10/c3.min.css">
    <link rel="stylesheet" type="text/css" href="{!!asset('css/dropzone.min.css')!!}">
    <link rel="stylesheet" type="text/css" href="{!!asset('css/dropzone.basic.min.css')!!}">
    <link rel="stylesheet" type="text/css" href="{!!asset('css/ooh-planning.css')!!}">
    <link href='https://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>

    @stack('css')
    @stack('scripts')
</head>
<body id="kt_body" class="header-tablet-and-mobile-fixed aside-enabled">
    <div class="d-flex flex-column flex-root">
        <div class="page d-flex flex-row flex-column-fluid">
            <div id="kt_aside" class="aside sidebar" data-kt-drawer="true" data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_mobile_toggle">
                    <div class="sidebar-nav navbar-collapse">
                        <ul class="nav" id="side-menu">
                            <li>
                                <a href="{{url('brands/index')}}"><i class="fas fa-tachometer-alt fa-1x" aria-hidden="true"></i>Brands</a>
                            </li>
                            <li>
                                <a href="{{url('seller/index')}}"><i class="fas fa-tachometer-alt fa-1x" aria-hidden="true"></i>Sellers</a>
                            </li>
                        </ul>
                    </div>
            </div>
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                <div id="kt_header" style="" class="header align-items-stretch">
                    <div class="header-brand">
                        <a href="{!!url('/')!!}">
                            <img alt="Logo" src="{!!asset('img/vicinity-logo-smaller.png')!!}" class="h-25px h-lg-25px" />
                        </a>
                        <div id="kt_aside_toggle" class="btn btn-icon w-auto px-0 btn-active-color-primary aside-minimize" data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="aside-minimize">
                            <span class="svg-icon svg-icon-1 me-n1 minimize-default">
                                <i class="fa fa-caret-left"></i>

                            </span>
                            <span class="svg-icon svg-icon-1 minimize-active">
                                <i class="fa fa-caret-right"></i>

                            </span>
                        </div>
                        <div class="d-flex align-items-center d-lg-none ms-n3 me-1" title="Show aside menu">
                            <div class="btn btn-icon btn-active-color-primary w-30px h-30px" id="kt_aside_mobile_toggle">
                                <span class="svg-icon svg-icon-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z" fill="black" />
                                        <path opacity="0.3" d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z" fill="black" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="toolbar">
                        <div class="w-100 p-15 py-6 py-lg-0 d-flex flex-column flex-lg-row align-items-lg-stretch justify-content-lg-between">
                            <div class="page-title d-flex flex-column me-5">
                                @if(request()->path() != "campaigns")
                                    <h1 class="d-flex flex-column text-dark fw-bolder fs-3 mb-0">@yield('header')</h1>
                                @else
                                    <h1 class="d-flex flex-column text-dark fw-bolder fs-3 mb-0" id="headerCampaignName"></h1>
                                @endif
                                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 pt-1">
                                    <li class="breadcrumb-item text-muted">
                                        <!--<a href="{!!url('/')!!}" class="text-muted text-hover-primary">Home</a>-->
                                    </li>
                                    <li class="breadcrumb-item">
                                        <span class="bullet bg-gray-200 w-5px h-2px"></span>
                                    </li>
                                    <li class="breadcrumb-item text-dark">@if(request()->path() != "campaigns") @yield('header') @else Campaign @endif</li>
                                </ul>
                            </div>
                            <!--begin::User-->
                            <div class="d-flex align-items-center pt-3 pt-lg-0">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-50px">

                                </div>
                                <!--end::Symbol-->
                                <!--begin::Wrapper-->
                                <div class="flex-row-fluid flex-wrap ms-5">
                                    <!--begin::Section-->
                                    <div class="d-flex">
                                        <!--begin::Info-->
                                        <div class="flex-grow-1 me-2">
                                            <!--begin::Username-->
                                            <a href="#" class="text-hover-primary fs-6 fw-bold">User</a>
                                            <div class="d-flex align-items-center text-success fs-9">
                                                <span class="bullet bullet-dot bg-success me-1"></span>online
                                            </div>
                                            <div class="me-n2">
                                                <a href="#" class="btn btn-icon btn-sm btn-active-color-primary justify-content-start mt-n2" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start" data-kt-menu-overflow="true">

                                                    <span class="svg-icon svg-icon-muted svg-icon-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <path opacity="0.3" d="M22.1 11.5V12.6C22.1 13.2 21.7 13.6 21.2 13.7L19.9 13.9C19.7 14.7 19.4 15.5 18.9 16.2L19.7 17.2999C20 17.6999 20 18.3999 19.6 18.7999L18.8 19.6C18.4 20 17.8 20 17.3 19.7L16.2 18.9C15.5 19.3 14.7 19.7 13.9 19.9L13.7 21.2C13.6 21.7 13.1 22.1 12.6 22.1H11.5C10.9 22.1 10.5 21.7 10.4 21.2L10.2 19.9C9.4 19.7 8.6 19.4 7.9 18.9L6.8 19.7C6.4 20 5.7 20 5.3 19.6L4.5 18.7999C4.1 18.3999 4.1 17.7999 4.4 17.2999L5.2 16.2C4.8 15.5 4.4 14.7 4.2 13.9L2.9 13.7C2.4 13.6 2 13.1 2 12.6V11.5C2 10.9 2.4 10.5 2.9 10.4L4.2 10.2C4.4 9.39995 4.7 8.60002 5.2 7.90002L4.4 6.79993C4.1 6.39993 4.1 5.69993 4.5 5.29993L5.3 4.5C5.7 4.1 6.3 4.10002 6.8 4.40002L7.9 5.19995C8.6 4.79995 9.4 4.39995 10.2 4.19995L10.4 2.90002C10.5 2.40002 11 2 11.5 2H12.6C13.2 2 13.6 2.40002 13.7 2.90002L13.9 4.19995C14.7 4.39995 15.5 4.69995 16.2 5.19995L17.3 4.40002C17.7 4.10002 18.4 4.1 18.8 4.5L19.6 5.29993C20 5.69993 20 6.29993 19.7 6.79993L18.9 7.90002C19.3 8.60002 19.7 9.39995 19.9 10.2L21.2 10.4C21.7 10.5 22.1 11 22.1 11.5ZM12.1 8.59998C10.2 8.59998 8.6 10.2 8.6 12.1C8.6 14 10.2 15.6 12.1 15.6C14 15.6 15.6 14 15.6 12.1C15.6 10.2 14 8.59998 12.1 8.59998Z" fill="black" />
                                                            <path d="M17.1 12.1C17.1 14.9 14.9 17.1 12.1 17.1C9.30001 17.1 7.10001 14.9 7.10001 12.1C7.10001 9.29998 9.30001 7.09998 12.1 7.09998C14.9 7.09998 17.1 9.29998 17.1 12.1ZM12.1 10.1C11 10.1 10.1 11 10.1 12.1C10.1 13.2 11 14.1 12.1 14.1C13.2 14.1 14.1 13.2 14.1 12.1C14.1 11 13.2 10.1 12.1 10.1Z" fill="black" />
                                                        </svg>
                                                    </span>
                                                </a>
                                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-bold py-4 fs-6 w-275px" data-kt-menu="true">
                                                    <div class="menu-item px-5">
                                                        <a href="https://wordpress.com/me" target="_blank" class="menu-link px-5">Profile Picture</a>
                                                    </div>
                                                    <div class="separator my-2"></div>
                                                    <div class="menu-item px-5">
                                                    <a href="#" class="menu-link px-5"><i class="fa fa-sign-out"></i> Logout</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::User-->
                        </div>
                    </div>
                </div>
                <div class="content d-flex flex-column flex-column-fluid " id="kt_content">
                    <div class="post d-flex flex-column fluid" id="kt_post">
                        <div class="w-100 p-15 pt-0" id="kt_content_container">
                            <div id="page-wrapper" >
                                <div class="row" id="errors">
                                    <div class="col-lg-12">
                                        @if(Session::has('success'))
                                            <div class="alert alert-success alert-dismissible">
                                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                {!!Session::get('success')!!}
                                            </div>
                                        @endif
                                        @if(Session::has('error'))
                                            <div class="alert alert-danger alert-dismissible">
                                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                {!!Session::get('error')!!}
                                            </div>
                                        @endif

                                        @if (count($errors->all()) > 0)
                                            <div class="alert alert-danger alert-dismissible">
                                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                @foreach ($errors->all() as $error)
                                                    {!! $error !!}<br>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    @yield('content')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
        <span class="svg-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="black" />
                <path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="black" />
            </svg>
        </span>
        <!--end::Svg Icon-->
    </div>
    <!--end::Scrolltop-->
    <!--end::Main-->

    <!-- New Layout -->

    <!-- Global(Metronic) -->
    <script src="{!!asset('assets/plugins/global/plugins.bundle')!!}.js"></script>
    <script src="{!!asset('assets/js/scripts.bundle')!!}.js"></script>

    <script src="{!!asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle')!!}.js"></script>

    <script type="text/javascript" src="{!!asset('js/jquery.js')!!}"></script>
    <script type="text/javascript" src="{!!asset('js/bootstrap.min.js')!!}"></script>
    <script type="text/javascript" src="{!!asset('js/script.js')!!}"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/c3/0.4.10/c3.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"></script>
    <script type="text/javascript" src="{!!asset('js/plugins/metisMenu/metisMenu.min.js')!!}"></script>
    <script type="text/javascript" src="{!!asset('bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js')!!}"></script>
    <script type="text/javascript" src="{!!asset('js/jquery-observe.js')!!}"></script>
    <script type="text/javascript" src="{!!asset('boostrap-switcher/js/bootstrap-switch.min.js')!!}"></script>
    <script type="text/javascript" src="{!!asset('Google-Web-Font-Selector/jquery.fontpicker.min.js')!!}"></script>
    <script type="text/javascript" src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/js/bootstrap-select.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
    <script type="text/javascript" src="{!!asset('/js/plugins/chartjs/Chart.bundle.js')!!}"></script>
    <script type="text/javascript" src="{!!asset('/js/plugins/chartjs/utils.js')!!}"></script>
    <script type="text/javascript" src="{!!asset('/js/plugins/chartjs/chartjs-plugin-zoom.js')!!}"></script>
    <script type="text/javascript" src="{!!asset('/js/plugins/chartjs/chartjs-plugin-draggable.js')!!}"></script>
    <script type="text/javascript" src="//www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="/js/plugins/pivottable/dist/pivot.js"></script>
    <script type="text/javascript" src="/js/plugins/pivottable/dist/tips_data.min.js"></script>
    <script type="text/javascript" src="/js/plugins/pivottable/dist/d3_renderers.js"></script>
    <script type="text/javascript" src="/js/plugins/pivottable/dist/gchart_renderers.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/pivottable/2.13.0/c3_renderers.min.js"></script>
    <script type="text/javascript" src="//www.google.com/jsapi"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/sweetalert"></script>
    @yield('chartResources')
    <script type="text/javascript" src="{!!asset('js/sb-admin-2.js')!!}"></script>
    <script type="text/javascript" src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{!!asset('js/plugins/dataTables/dataTables.bootstrap.js')!!}"></script>
    <script type="text/javascript" src="{!!asset('js/plugins/dataTables/DataTables.dataTableTools.js')!!}"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <!-- <script type="text/javascript" src="{!!asset('js/plugins/bootstrap/

        bootstrap-multiselect.js')!!}"></script> -->
    <script type="text/javascript" src="//cdn.datatables.net/fixedheader/3.1.2/js/dataTables.fixedHeader.min.js"></script>
    <script type="text/javascript" src="//cdn.datatables.net/buttons/1.4.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="//cdn.datatables.net/select/1.2.3/js/dataTables.select.min.js"></script>
    <script type="text/javascript" src="{!!asset('js/dropzone.js')!!}"></script>
    <script type="text/javascript" src="{!!asset('js/jquery.dataTables.yadcf.0.9.2.js')!!}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/1.8.2/autoNumeric.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/lodash.js/4.15.0/lodash.min.js"></script>
    <script src="https://unpkg.com/vue-multiselect@2.1.0"></script>
    <script src="https://unpkg.com/vue-multi-select@3.11.1"></script>
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/radar.js"></script>
    @yield('script')
    @if(Config::get('app.debug'))
<center><h5>{!!sprintf('%s (%s)', trim(exec('git log --pretty="%h" -n1 HEAD')), trim(substr(file_get_contents(base_path().'/.git/HEAD'), 16)) );!!}</h5></center>
@endif
</body>
</html>
