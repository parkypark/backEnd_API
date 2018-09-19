@extends('layouts.transmittal')
@section('content')
  <p>
    Please find enclosed
    @numberToWords($quantity)
    ({{$quantity}}) {{$quantity > 1 ? 'sets' : 'set'}} of Stamped Final drawings for your review.
  </p>

  <p>
    If Starline products are to be site tested, one of our technical representatives must be in attendance.
    We ask for a written request preferably 4 - 5 business days prior to the scheduled test date to allow arrangement for attendance on site.
  </p>

  <p>
    All site visits require inspection for installation compliance. We request the contractors to contact Starline, in writing, to schedule
    the site visit when the first floor or approximately &plusmn;25% of the project is complete. Please allow 7 - 10 business days notice to schedule this visit.
  </p>

  <p>
      Please review, accept and return one (1) set of drawings to this office within
      @numberToWords($required_days)
      ({{$required_days}}) business days of receipt by <strong class="text-red">{{$required_date}}</strong>.
      Any delay in returning the drawing may result in a delay in production. Starline is not responsible for
      project manufacturing delays if the above mentioned drawing is not returned within the specified timeline.
  </p>
@stop
