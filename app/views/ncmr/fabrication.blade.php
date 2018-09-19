<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="http://projects.starlinewindows.com/apps/static/api/bower_components/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="http://projects.starlinewindows.com/apps/static/api/bower_components/bootstrap/dist/css/bootstrap-theme.min.css">

<head>
    <style>
    html, body {
        height: 99%;
        margin: 0;
        padding: 0;
    }

    body {
        background: #fff;
        color: #000;
        font-family: "Cabin", Arial, sans-serif;
        font-size: 14px !important;
        padding-top: 15px;
        text-shadow: none;
    }

    table {background: #fff; height: 100% !important}

    #logo {height: 50px}

    .ncmr-header tr td {padding-top: 0; padding-bottom: 0; margin: 0; vertical-align: middle}
    .ncmr-header tr td, .ncmr-header tr th {border: solid 1px #000 !important}
    .ncmr-header h3 {margin: 0 !important}
    .ncmr-header .table {border: none; margin: 0}
    .ncmr-header .table td {
        border-top: none !important;
        border-bottom: none !important;
        vertical-align: middle;
    }
    .ncmr-header .table td:first-of-type {border-left: none !important}
    .ncmr-header .table td:last-of-type {border-right: none !important}

    .ncmr-info {margin-bottom: 0.5em}
    .ncmr-info h3, .ncmr-info h4 {margin-bottom: 0; margin-top: 0}

    .ncmr-details tr td {font-size: 12px; vertical-align: middle}
    .ncmr-details tr td, .ncmr-details tr th {border: solid 1px #000 !important}

    .ncmr-footer {margin-bottom: 2em}
    .ncmr-footer tr td {
        border-left: none; border-right: none;
        border-top: none; border-bottom: 1px solid black !important;
        min-width: 15em
    }
    .ncmr-footer tr th {padding-right: 1em}
    .ncmr-footer tr:not(:first-of-type) td, .ncmr-footer tr:not(:first-of-type) th {padding-top: 2em}
    .ncmr-footer tr th:not(:first-of-type) {padding-left: 4em}

    .rsvp {
        border-color: black;
        border-width: 4px;
        border-style: double;
        padding: 1em;
    }
    .rsvp h4 {margin: 0; line-height: 1.25}

    .text-middle {vertical-align:middle !important}

    @media print{
        @page {size: landscape}
    }
    </style>
</head>

<body>
    <div class="container">
        <table class="table table-bordered table-condensed ncmr-header">
            <tr>
                <td class="col-xs-3" colspan="2">Starline Windows</td>
                <td rowspan="3" class="col-xs-6 text-center" style="padding: 0">
                    <table class="table">
                        <tr>
                            <td class="col-xs-6">
                                <img id="logo" src="http://projects.starlinewindows.com/apps/static/api/img/starline_logo.png">
                            </td>
                            <td class="text-center"><h3>Form</h3></td>
                        </tr>
                    </table>
                </td>
                <td class="text-right">DOCUMENT:</td>
                <td class="text-right">QF705-8</td>
            </tr>
            <tr>
                <td colspan="2">19091 &ndash; 36th Ave, Surrey, BC V3Z 0P6</td>
                <td class="text-right">REVISION:</td>
                <td class="text-right">1</td>
            </tr>
            <tr>
                <td>Tel: 604-882-5100</td>
                <td>Fax: 604-882-5102</td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <div class="ncmr-info">
            <div class="row">

            </div>
            <div class="row">
                <div class="col-xs-8">
                    <h4>Non-conforming Materials Report (Fabrication)</h4>
                </div>
                <div class="col-xs-4">
                    <h4 class="text-right">Report #: {{$ncmr['report_number']}}</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <h4>Supplier: {{$ncmr['supplier']}}</h4>
                </div>
                <div class="col-xs-6">
                    <h4 class="text-right">Report Date: {{date('Y-M-d', strtotime($ncmr['report_date']))}}</h4>
                </div>
            </div>
        </div>

        <table class="table table-bordered table-condensed ncmr-details">
            <thead>
            <tr>
                <th class="text-right">#</th>
                <th>Project</th>
                <th class="text-center">List #</th>
                <th class="text-center">Frame #</th>
                <th class="text-center">Material</th>
                <th class="text-center">Colour</th>
                <th class="text-center">Size</th>
                <th class="text-center">Reason to Reject</th>
                <th class="text-right">Qty. Rejected</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($ncmr['details'] as $index => $line)
            <tr>
                <td class="text-right text-nowrap">{{$index + 1}}</td>
                <td>{{$line['project_name']}} (#{{$line['project_id']}})</td>
                <td class="text-center text-nowrap">{{$line['list_number']}}</td>
                <td class="text-center text-nowrap">{{$line['frame_number']}}</td>
                <td class="text-center text-nowrap">{{$line['material']}}</td>
                <td class="text-center text-nowrap">{{$line['colour']}}</td>
                <td class="text-center text-nowrap">
                    <span>{{$line['width']}}&#8243; x {{$line['height']}}&#8243;</span>
                    @if (isset($line['depth']) && $line['depth'] > 0)
                    <span>x {{$line['depth']}}&#8243;</span>
                    @endif
                </td>
                <td class="text-center">{{$line['rejected_reason']}}</td>
                <td class="text-right text-nowrap">{{$line['rejected']}}</td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td colspan="8" class="text-right">Total Rejected:</td>
                <td class="text-right">{{$total_rejected}}</td>
            </tr>
            <tr>
                <td colspan="9"><strong>Comments: </strong>{{$ncmr['comment']}}</td>
            </tr>
            </tfoot>
        </table>

        <br>

        <table class="ncmr-footer">
            <tbody>
            <tr>
                <th>Inspected By:</th>
                <td class="text-center">{{$ncmr['inspector']}}</td>
                <th>Inspection Date:</th>
                <td class="text-center">{{date('Y-M-d', strtotime($ncmr['date_inspected']))}}</td>
                <th class="col-xs-4" colspan="2" rowspan="6">
                    <div class="rsvp">
                        <h4 class="text-center">Please respond with Corrective Action Report within two weeks of receiving this notice.</h4>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Sample Plan:</th>
                <td class="text-center">100% Inspection</td>
                <th>Form Filled Out By:</th>
                <td class="text-center">{{$ncmr['report_filled_by']}}</td>
            </tr>
            <tr>
                <th>Bundle Ref #:</th>
                <td class="text-center"><h5 style="margin:0">{{$ncmr['bundle_number']}}</h5></td>
                <th>Quantity in Bundle:</th>
                <td class="text-center"><h5 style="margin:0">{{$ncmr['bundle_qty']}}</h5></td>
            </tr>
            <tr>
                <th>Quality Manager:</th>
                <td></td>
                <th>Materials Manager:</th>
                <td></td>
            </tr>
            <tr>
                <th>Replacement P.O.:</th>
                <td class="text-center"></td>
                <th>Date Material Returned:</th>
                <td class="text-center"></td>
            </tr>
            <tr>
                <th>Driver's Signature:</th>
                <td class="text-center"></td>
            </tr>
            </tbody>
        </table>

        @if (count($ncmr['attachments']) > 0)
        <div id="attachments">
            @foreach ($ncmr['attachments'] as $attachment)
            <img class="img-responsive center-block" src="{{$attachment['data']}}" style="padding-top:30px">
            @endforeach
        </div>
        @endif
    </div>
</body>

</html>
