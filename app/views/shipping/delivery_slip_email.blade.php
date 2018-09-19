<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <style>
      table {
        border-collapse: collapse;
        margin-bottom: 2em;
        width: 100%;
      }

      table:not(.borderless) thead th {
        background: #ccc;
      }

      table tr td, table tr th {
        border: solid 1px black;
        padding: 4px 8px;
        text-align: center;
        vertical-align: top;
      }

      table.borderless tr td, table.borderless tr th,
      table.signature tr td, table.signature tr th {
        border: none;
      }

      table.signature tr td {
        border-bottom: solid 1px black;
      }
      table.signature tr th {
        vertical-align: bottom;
      }

      #wrapper {
        max-width: 1100px;
        margin: 0 auto;
      }

      #header {
        width: 100%;
      }
      #header tr td, #header tr th {
        border: none;
        border-bottom: solid 1px black;
      }
      #header h1 {
        margin: 0;
      }
      #header img {
        max-height: 100px;
        width: auto;
      }

      #photos td {
        text-align: center;
      }
      #photos img {
        max-width: 100%;
        height: auto;
      }

      .text-bottom {
        vertical-align: bottom;
      }
      .text-left {
        text-align: left;
      }
      .text-right {
        text-align: right;
      }
    </style>
  </head>

  <body>
    <div id="wrapper">
      <table id="header">
        <tr>
          <th class="text-left text-bottom">
            <h1>Delivery Slip</h1>
          </th>
          <td class="text-right">
            <img src="{{ $message->embed(public_path().'/img/starline_logo_100px.jpg') }}">
          </td>
        </tr>
      </table>

      <table class="borderless">
        <tr>
          <td>Order #{{ $order_number }}</td>
          <td class="text-right">{{ $delivery_date }}</td>
        </tr>
      </table>

      <table class="borderless">
        <tr>
          <th class="text-right" rowspan="3" style="width:50%">Ship To</th>
          <td class="text-right">{{ $customer_name }}</td>
        </tr>
        <tr>
          <td class="text-right">{{ $customer_address }}</td>
        </tr>
        <tr>
          <td class="text-right">{{ $customer_city }}</td>
        </tr>
        <tr>
          <th class="text-right" style="width:50%">Driver</th>
          <td class="text-right">{{ $employee_id }}</td>
        </tr>
      </table>

      <table>
        <thead>
          <tr>
            <th class="text-left">Item #</th>
            <th class="text-left">Description</th>
            <th class="text-left">Scan Time</th>
            <th class="text-left">Location</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($items as $item)
            <tr>
              <td class="text-left">{{ $item['boxnumber'] > 100 ? $item['boxnumber'] : $item['linenumber'] }}</td>
              <td class="text-left">{{ $item['itemdescription'] }}</td>
              <td class="text-left">{{ date('Y-m-d h:i:s A', strtotime($item['scantime'])) }}</td>
              <td class="text-left">{{ round($location[0], 2) }}, {{ round($location[1], 2) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <th class="text-left" colspan="3">Total Delivered</th>
          <td class="text-left">{{ count($items) }} / {{ $total_items }}</td>
        </tfoot>
      </table>

      @if($photos && count($photos) > 0)
        <table id="photos" class="borderless">
          <tr>
            @foreach ($photos as $photo)
              <td><img src="{{ $message->embed($photo) }}"></td>
            @endforeach
          </tr>
        </table>
      @endif

      @if($comments)
        <table>
          <thead>
            <tr>
              <th>Comments</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-left">{{ $comments }}</td>
            </tr>
          </tbody>
        </table>
      @endif

      <table class="signature">
        <tr>
          <th class="text-right">Received By</th>
          <td>{{ $receiver_name }}</td>
          <td style="width:45%">
            <img id="signature" src="{{ $message->embed($signature) }}">
          </td>
        </tr>
      </table>
    </div>
  </body>
</html>
