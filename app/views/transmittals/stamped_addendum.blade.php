@extends('layouts.transmittal')
@section('content')
  <p>
    Please find enclosed
    @numberToWords($quantity)
    ({{$quantity}}) {{$quantity > 1 ? 'sets' : 'set'}} of Stamped Addendum #{{$addendum_number}} drawings for your review/approval or use.
  </p>

  <p>
      Please review, accept and return one (1) set of drawings to this office within
      @numberToWords($required_days)
      ({{$required_days}}) business days of receipt by <strong class="text-red">{{$required_date}}</strong>.
      Any delay in returning the drawing may result in a delay in production. Starline is not responsible for
      project manufacturing delays if the above mentioned drawing is not returned within the specified timeline.
  </p>
@stop
