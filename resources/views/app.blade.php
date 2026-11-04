<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
        <link rel="icon" type="image/png" href="{{ asset('assets/img/pos-logo.png') }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
  
        <!-- Fonts -->
        <!-- <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> -->
        
        <!-- Custom fonts for this template-->
        <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/css/fonts.googleapis.css') }}" rel="stylesheet">

        <!-- Custom styles for this template-->
        <link href="{{ asset('assets/css/sb-admin-2.min.css') }}" rel="stylesheet">

        <!-- Select2 CSS -->
        <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />

        <!-- DataTables CSS -->
        <link rel="stylesheet" href="{{ asset('assets/css/jquery.dataTables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/datatables/datatables.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/datatables/datatables.min.css') }}">

        <link rel="stylesheet" href="{{ asset('assets/css/buttons.dataTables.min.css') }}">

        <style type="text/css">
            .dataTables_length select {
                width: 66px;
            }

            /* Increase height of the Select2 selection box */
            .select2-container--default .select2-selection--single {
                height: auto;
            }
        </style>
    
        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body id="page-top" class="sidebar-toggled">
        @inertia

        <!-- Bootstrap core JavaScript-->
        <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

        <!-- Core plugin JavaScript-->
        <script src="{{ asset('assets/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

        <!-- Custom scripts for all pages-->
        <script src="{{ asset('assets/js/sb-admin-2.min.js') }}"></script>

        <!-- jQuery & DataTables JS -->
         <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
        <!--<script src="{{ asset('assets/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('assets/js/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('assets/js/buttons.print.min.js') }}"></script>
        <script src="{{ asset('assets/js/jszip.min.js') }}"></script>
        <script src="{{ asset('assets/js/pdfmake.min.js') }}"></script>
        <script src="{{ asset('assets/js/vfs_fonts.js') }}"></script> 
        <script src="{{ asset('assets/datatables/datatables.js') }}"></script>
        <script src="{{ asset('assets/datatables/datatables.min.js') }}"></script> -->

        <!-- Select2 JS -->
        <script src="{{ asset('assets/js/select2.min.js') }}"></script>


    </body>
    
    
</html>
