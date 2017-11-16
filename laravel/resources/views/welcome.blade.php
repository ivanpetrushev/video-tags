<!doctype html>
<html>
<head>
    <title>Интересни места</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css"
          integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.2.0/dist/leaflet.css"
          integrity="sha512-M2wvCLH6DSRazYeZRIm1JnYyh22purTM+FDB5CsyxtQJYeKq83arPe5wgbNmcFXGqiSH2XR8dT/fJISVA1r/zQ=="
          crossorigin=""/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/css/styles.css?ts=<?= time() ?>"/>
</head>
<body>

<div class="wrapper">
    <div class="list-group" id="sidebar">
        <span href="#" class="list-group-item active">
            Интересни места
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#search-form">
                <span class="fa fa-reorder" aria-hidden="true"></span>
            </button>
        </span>
        <span href="#" class="list-group-item search">
            <div class="input-group input-group-unstyled">
                <input type="text" class="form-control" />
                <span class="input-group-addon">
                    <i class="fa fa-search"></i>
                </span>
            </div>
        </span>

    </div>

    <div id="mapid"></div>
</div>

<div id="search-form" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Търсене</h4>
            </div>
            <div class="modal-body">
                <h4>Категории</h4>
                <div class="category-list row"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary">Филтрирай</button>
            </div>
        </div>

    </div>
</div>


<script src="https://unpkg.com/leaflet@1.2.0/dist/leaflet.js"
        integrity="sha512-lInM/apFSqyy1o6s89K4iQUKg6ppXEgsVxT35HbzUupEVRh2Eu9Wdl4tHj7dZO0s1uvplcYGmt3498TtHq+log=="
        crossorigin=""></script>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"
        integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js"
        integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ"
        crossorigin="anonymous"></script>
<script src="/js/leaflet.uGeoJSON.js"></script>
<script src="/js/jquery.tristate.js"></script>
<script src="/js/scripts.js?ts=<?= time() ?>"></script>
</body>
</html>