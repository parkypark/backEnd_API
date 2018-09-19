<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="http://projects.starlinewindows.com/apps/static/api/bower_components/bootstrap/dist/css/bootstrap.min.css">

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
    font-family: "Cabin", "Arial", sans-serif !important;
    font-size: 14px !important;
    padding-top: 15px;
    text-shadow: none;
  }

  table {
    background: #fff;
    border-collapse: collapse;
    width: 100%;
    height: 100% !important;
  }

  #logo {height: 50px}

  .ncmr-header>.table>tbody>tr>td,
  .ncmr-header>.table>tbody>tr>th,
  .ncmr-header>.table>tfoot>tr>td,
  .ncmr-header>.table>tfoot>tr>th,
  .ncmr-header>.table>thead>tr>td,
  .ncmr-header>.table>thead>tr>th {
    border: solid 1px #000;
  }
  .ncmr-header h3 {margin: 0 !important}
  .ncmr-header td>.table {
    margin: 0;
  }
  .ncmr-header td>.table tr>td:first-of-type {border-right: solid 1px black}

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

  .no-padding {padding: 0 !important}
  .text-middle {vertical-align:middle !important}

  @media print{
    @page {size: landscape}
  }
  </style>
</head>

<body>
    <div class="container">
      <div class="ncmr-header">
        <table class="table">
          <tr>
            <td colspan="2">Starline Windows</td>

            <td class="col-xs-4 no-padding" rowspan="3">
              <table class="table">
                <tr>
                  <td class="col-xs-6 text-center text-middle"><img id="logo" src="http://projects.starlinewindows.com/apps/static/api/img/starline_logo.png"></td>
                  <td class="col-xs-6 text-center text-middle"><h3>Form</h3></td>
                </tr>
              </table>
            </td>

            <td class="text-right">REVISION DATE:</td>
            <td class="text-right">April 28, 2011</td>
          </tr>

          <tr>
            <td colspan="2">19091 &ndash; 36th Ave, Surrey, BC V3Z 0P6</td>
            <td class="text-right">DOCUMENT:</td>
            <td class="text-right">QF705-1</td>
          </tr>

          <tr>
            <td>Tel: 604-882-5100</td>
            <td>Fax: 604-882-5102</td>
            <td class="text-right">REVISION:</td>
            <td class="text-right">2</td>
          </tr>
        </table>
      </div>

        <div class="ncmr-info">
            <div class="row">
                <div class="col-xs-6">
                    <h4>Non-conforming Materials Report (External)</h4>
                </div>
                <div class="col-xs-6">
                    <h4 class="text-right">Report #: {{$ncmr['report_number']}}</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-4">
                    <h4>Supplier: {{$ncmr['supplier']}}</h4>
                </div>
                <div class="col-xs-4">
                    <h4>Destination: {{$ncmr['destination']}}</h4>
                </div>
                <div class="col-xs-4">
                    <h4 class="text-right">Report Date: {{$ncmr['report_date']}}</h4>
                </div>
            </div>
        </div>

        <table class="table table-bordered table-condensed ncmr-details">
            <thead>
            <tr>
                <th class="text-right">#</th>
                <th class="text-center">Date Received</th>
                <th class="text-center">Die Number</th>
                <th class="text-center">Length</th>
                <th class="text-center">Colour</th>
                <th class="text-center">PO Number</th>
                <th class="text-center">RCV Number</th>
                <th class="text-center">Discrepancy</th>
                <th class="text-right">Qty. Picked</th>
                <th class="text-right">Qty. Rejected</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($ncmr['details'] as $index => $detail)
                <tr>
                    <td class="text-right text-nowrap">{{$index + 1}}</td>
                    <td class="text-center text-nowrap">{{date('M d, Y', strtotime($detail['date_received']))}}</td>
                    <td class="text-center text-nowrap">{{$detail['die_number']}}</td>
                    <td class="text-center text-nowrap">{{$detail['stock_length']}}&Prime;</td>
                    <td class="text-center text-nowrap">{{$detail['colour']}}</td>
                    <td class="text-center text-nowrap">{{$detail['po_number']}}</td>
                    <td class="text-center text-nowrap">{{$detail['rcv_number']}}</td>
                    <td class="text-center text-nowrap">{{$detail['discrepancy']}}</td>
                    <td class="text-right text-nowrap">{{$detail['picked']}}</td>
                    <td class="text-right text-nowrap">{{$detail['rejected']}}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            @foreach (explode("\n", $ncmr['comment']) as $index => $comment)
                @if (strlen(trim($comment)) > 0)
                    <tr>
                        <td colspan="10">
                            @if ($index === 0)
                                <strong>Comments: </strong> {{$comment}}
                                <ul class="list-inline pull-right" style="margin:0">
                                    @if (array_key_exists('sort_time', $ncmr))
                                        <li>Sort Time: {{$ncmr['sort_time']}} h</li>
                                    @endif
                                    <li>Total Rejected: {{$total_rejected}}</li>
                                </ul>
                            @else
                                {{$comment}}
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
            </tfoot>
        </table>

        <br>

        <table class="ncmr-footer">
            <tbody>
            <tr>
                <th>Inspected By:</th>
                <td class="text-center">{{$ncmr['inspector']}}</td>
                <th>Sample Plan:</th>
                <td class="text-center">100% Inspection</td>
            </tr>
            <tr>
                <th>Quality Manager:</th>
                <td></td>
                <th>Quantity in Bundle:</th>
                <td class="text-center"><strong class="h5" style="margin:0">{{$total_rejected}}</strong></td>
            </tr>
            <tr>
                <th>Materials Manager:</th>
                <td></td>
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
