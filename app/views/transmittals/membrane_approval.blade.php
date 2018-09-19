@extends('layouts.transmittal')
@section('content')
  <p>
      Please find enclosed one (1) specification sheet and one (1) sample of Protecto Wrap {{$membrane}} as part of the
      sealant submittal and approval process.
  </p>

  <p>
      Please sign and date below to acknowledge approval and return within
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
