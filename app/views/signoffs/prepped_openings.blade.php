<!DOCTYPE html>
<html lang="en">
  <head>
    <style>
      html, body {
        font-family: sans-serif;
        font-size: 10px;
      }

      h1 {
        margin: 0;
      }

      p {
        white-space: normal;
      }

      table {
        border-collapse: collapse;
        border-spacing: 0;
        margin-bottom: 1em;
        page-break-inside: avoid;
        width: 100%;
      }

      table tr th {
        border: solid 1px black;
        padding: 4px 8px;
        text-align: left;
        white-space: nowrap;
      }

      td.text-right {
        padding-right: .5em;
      }

      tr.header td {
        padding: 4px 8px;
        text-align: center;
        white-space: nowrap;
      }

      tr.header td:not(:nth-of-type(1)):not(:nth-of-type(2)) {
        border: solid 1px black;
      }

      .bordered {
        border: solid 1px black;
      }

      .check {
        border: solid 1px black;
        height: 2em;
        margin: 0;
        min-height: 2em;
        min-width: 2em;
        padding: 4px;
        text-align: center;
        width: 2em;
      }

      .field {
        min-width: 10em;
      }

      .label {
        padding-right: 1em;
        text-align: right;
      }

      .flex {
        width: 99%;
      }

      .logo {
        min-width: 200px;
      }

      .rotated-container {
        width: 3em;
        max-width: 3em;
        max-height: 3em;
        padding: 0;
      }

      .rotated {
        text-align: center;
        -webkit-backface-visibility: hidden;
        -webkit-transform: rotate(-90deg);
        transform: rotate(-90deg);
      }

      .text-center {
        text-align: center !important;
      }

      .text-right {
        text-align: right !important;
      }

      .title {
        font-size: 16px;
        text-align: center;
      }

      .page-content {
        font-size: 10px;
      }
      .page-content td {
        padding: 4px 8px;
        white-space: nowrap;
      }

      .page-header table {
        font-size: 14px;
        margin-bottom: 2em;
      }
      .page-header table table {
        width:100%;
        height:97px;
        margin:0
      }
      .page-header table table tr th {
        border: 0;
      }
      .page-header table table tr:first-of-type th {
        border-bottom: solid 1px black;
      }
      .page-header table table tr th:first-of-type {
        border-right: solid 1px black;
      }

      .page-wrapper {
        display: table;
        width: 100%;
      }
      .page-wrapper:not(:last-of-type) .page-content table {
        page-break-after: always;
      }
    </style>
  </head>
  <body>
    @foreach($signoffs as $page => $data)
      <div class="page-wrapper">
        <div class="page-header">
          <table>
            <tr>
              <th class="logo">
                <img src="data:image/png;base64,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" />
              </th>
              <th class="flex title"><h1>Sign Off Form</h1></th>
              <th style="padding: 0">
                <table>
                  <tr>
                    <th class="text-right">Revision:</th>
                    <th>2</th>
                  </tr>
                  <tr>
                    <th class="text-right">Document:</th>
                    <th>QF510-1</th>
                  </tr>
                </table>
              </th>
            </tr>
            <tr>
              <th style="border-right:0"></th>
              <th class="title" style="border-left:0;border-right:0">Prepped Opening Review</th>
              <th style="border-left:0"></th>
            </tr>
          </table>

          <table class="borderless">
            <tr>
              <td class="label">Project Name:</td>
              <td class="field">{{$data['project_name']}}</td>
              <td class="label">Building:</td>
              <td class="field">{{$data['building_name']}}</td>
              <td class="label">Level:</td>
              <td class="field">{{$data['floor_name']}}</td>
            </tr>
            <tr>
              <td class="label">Field Quality Assurance Technician:</td>
              <td class="field">{{$data['technician']}}</td>
            </tr>
          </table>
        </div>

        <div class="page-content">
          <table>
            <!-- Dates -->
            <tr class="header">
              <td></td> <!-- intentionally blank -->
              <td class="text-right">Review Date:</td>
              @foreach ($data['windows'] as $window_name => $inspection_date)
                <td><div>{{$inspection_date}}</div></td>
              @endforeach
            </tr>

            <!-- Tags -->
            <tr class="header">
              <td></td> <!-- intentionally blank -->
              <td class="text-right">Window Tag:</td>
              @foreach ($data['windows'] as $window_name => $inspection_date)
                <td><div>{{$window_name}}</div></td>
              @endforeach
            </tr>

            <!-- Signoffs -->
            @foreach ($data['inspections'] as $category => $inspections)
              <tr>
                <th rowspan="{{count($inspections)}}" class="rotated-container">
                  <div class="rotated">{{$category}}</div>
                </th>
                <td class="bordered">{{key($inspections)}}</td>
                @foreach (reset($inspections) as $inspection)
                  <td class="check">
                    @if ($inspection === "1")
                      <span>&#10004;</span>
                    @endif
                  </td>
                @endforeach
              </tr>
              <?php $inspection_keys = array_keys($inspections) ?>
              @for($i = 1; $i < count($inspection_keys); ++$i)
                <tr>
                  <td class="bordered">{{$inspection_keys[$i]}}</td>
                  @foreach ($inspections[$inspection_keys[$i]] as $inspection)
                    <td class="check">
                      @if ($inspection === "1")
                        <span>&#10004;</span>
                      @endif
                    </td>
                  @endforeach
                </tr>
              @endfor
            @endforeach
            <tfoot>
              <tr>
                <td colspan="{{count($data['windows']) + 2}}" class="bordered">
                  <p>
                    <strong style="margin-right:1em;">Notes:</strong>
                    @foreach ($data['notes'] as $note)
                      <span style="margin-right:.5em;">{{$note}};</span>
                    @endforeach
                  </p>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    @endforeach
  </body>
</html>
