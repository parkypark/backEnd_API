@extends('layouts.transmittal')
@section('content')
  <p>
      Please find enclosed
      @for($i = 0; $i < count($samples); ++$i)
        @if($i > 0) &amp; @endif
        @numberToWords($samples[$i]->quantity)
        ({{$samples[$i]->quantity}})
        {{$samples[$i]->quantity > 1 ? 'samples' : 'sample'}}
        {{"of {$samples[$i]->name} {$samples[$i]->code} to meet AAMA {$samples[$i]->aama} specifications for the"}}
        {{strtolower($samples[$i]->type) . ' frame finish' . ($i === count($samples) - 1 ? '.' : '')}}
      @endfor
  </p>

  <p class="text-em">
      Please note that a metallic color approval has an eight (8) week &amp; non-metallic color approval has a three
      (3) week lead time for powder to be received for painting of material from the date the signed chip is received
      by this office.
  </p>

  <p class="text-center text-em text-strong">
      ABOVE LEAD TIMES DO NOT INCLUDE FOR MANUFACTURING!
  </p>

  <p class="text-em text-strong">
      Please note that actual color locations are to be specified on shop drawings.
  </p>

  <p>
      If approved, please sign the back of the chip, where indicated, and return to this office within @numberToWords($required_days)
      ({{$required_days}}) days business days of receipt by <strong class="text-red">{{$required_date}}</strong>.
      Please note that final stamped shop drawings cannot be provided unless this submittal form is signed as
      approved and returned.
  </p>

  <table>
    <thead>
      <tr class="bordered-bottom">
        <th>Sample</td>
        <th>Color</th>
        <th class="text-right">AAMA</td>
      </tr>
    </thead>

    <tbody>
      @foreach($samples as $sample)
        <tr>
          <td>{{$sample->type}}</td>
          <td>{{$sample->name}} {{$sample->code}}</td>
          <td class="text-right">{{$sample->aama}}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <table class="text-large" style="margin-top:1em">
    <tr>
      <td class="col-min text-nowrap" style="padding-right:2em">Samples Received and Approved by:</td>
      <td class="bordered-bottom"></td>
    </tr>
  </table>
@stop
