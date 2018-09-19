@extends('layouts.transmittal')
@section('content')
  <p>
      Please find enclosed one (1) specification sheet and one (1) colour sample of {{$sealant_type}} as part of the
      sealant submittal and approval process.
  </p>

  <table class="text-large">
    <tr>
      <td class="col-min text-nowrap" style="padding-top:1em; padding-right:2em" rowspan="2">Colour Section:</td>
      <td class="bordered-bottom" style="height:2em"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>

    <tr>
      <td class="col-min text-nowrap" style="padding-top:1em; padding-right:2em" rowspan="4">Received and Approved by:</td>
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

  <p>
      Please review, accept and return this form within
      @numberToWords($required_days)
      ({{$required_days}}) business days of receipt by <strong class="text-red">{{$required_date}}</strong>.
      Please note that final stamped shop drawings cannot be provided unless this submittal form is signed as approved and returned.
  </p>
@stop
