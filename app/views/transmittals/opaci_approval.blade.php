@extends('layouts.transmittal')
@section('content')
  <p>
      Please find enclosed the following Opaci-Coat sample{{count($samples) > 1 ? 's' : ''}} for the above noted project:
  </p>

  <table>
    <tr>
      <th></th>
      <th class="text-center">Accept</th>
      <th class="text-center">Reject</th>
    </tr>

    @foreach($samples as $sample)
      <tr>
        <td style="padding-left: 2em">{{$sample->code}} {{$sample->description}}</td>
        <td class="text-center">&#x2610;</td>
        <td class="text-center">&#x2610;</td>
      </tr>
    @endforeach
  </table>

  <p>
      Please sign and date below to acknowledge approval of {{count($samples) > 1 ? 'these samples' : 'this sample'}} and return within
      @numberToWords($required_days)
      ({{$required_days}}) business days of receipt by <strong class="text-red">{{$required_date}}</strong>.
      Please note that final stamped shop drawings cannot be provided unless this submittal form is signed as approved and returned.
  </p>

  <table class="text-large" style="margin-top:1em">
    <tr>
      <td class="col-min text-nowrap" style="padding-top:1em; padding-right:2em" rowspan="4">Samples Received and Approved by:</td>
      <td class="bordered-bottom" style="height:2em"></td>
    </tr>
    <tr>
      <td>Signature</td>
    </tr>
    <tr>
      <td class="bordered-bottom" style="height:2em"></td>
    </tr>
    <tr>
      <td>Date</td>
    </tr>
  </table>
@stop
