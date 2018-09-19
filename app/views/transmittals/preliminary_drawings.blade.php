@extends('layouts.transmittal')
@section('content')
  <p>
    Please find enclosed
    @numberToWords($quantity)
    ({{$quantity}}) {{$quantity > 1 ? 'sets' : 'set'}} of Preliminary drawings for your review.
  </p>

  <p>
    To be considered "Reviewed", all pertinent design and coordination issues requested within the Preliminary Shop Drawings must be addressed.
  </p>

  <ol>
    <li>Rough Openings &amp; Clearances</li>
    <li>Configurations and Handing</li>
    <li>Window / Door Details</li>
    <li>All sections on page 2.1 with special attention to section 2.2 thru 2.4</li>
    <li>The RCP/Code Consultant must address all items in 1.4.6</li>
    <li>The Prime Consultant must address all items as specified in 1.4 on page 2.1 and as indicated</li>
    <li>The Structural Engineer must address 1.2 on page 3.1</li>
    <li>The Building Envelope Consultant must address 2.1 on page 3.1 and as indicated on details</li>
    <li>The Mechanical Consultant must address 2.4 on page 3.1</li>
    <li>Any other consultant that may review the drawings must review this submittal</li>
    <li>The Contractor must address all items as specified in 1.5 on page 2.1</li>
  </ol>

  <p>
    Items not addressed will be considered as accurate and that no project criteria are specified within the construction
    documents and/or applicable codes; therefore, Starline's Standard Practice shall govern.
  </p>

  <p>
    Please review and ensure that the projects civil and legal addresses are correct and that the building permit number
    is provided; as these are necessary for issuance of the schedules.
  </p>

  <p>
      Please review, accept and return one (1) set of drawings to this office within
      @numberToWords($required_days)
      ({{$required_days}}) business days of receipt by <strong class="text-red">{{$required_date}}</strong>.
  </p>
@stop
