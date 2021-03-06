<div class="tab-pane fade p-lg-4 p-1 show active" id="pills-payment" role="tabpanel" aria-labelledby="pills-payment-tab">
    @if (session('error'))
        <div class="alert alert-danger" role="alert">
        {{session('error')}}
        </div>
    @endif
    <form method="post" action="{{action('CheckoutController@finishCheckout')}}" id="payment_choose_form">
        @csrf
        <div class="list-group list-group-flush">
            <div class="list-group-item p-0 row pb-3">
                @foreach (Auth::user()->cart() as $item)
                    @include("partials.checkoutItemCard",array("item" => $item))
                @endforeach
            </div>
            <div class="text-center mt-5 fs-4">
                <p class="mb-1"><span>Shipping: </span></p>
                <p><span id="shippingPayment" class="fs-5">{{$shipping_option['name'] . ' - ' . $shipping_option['price']}}</span></p>

                <p class="mb-1"><span>Total Payment: </span></p>
                <p><span id="totalPayment" class="fs-2" style="color:red">{{Auth::user()->cartTotalShipping($shipping_option['shipping_id'])}}</span></p>
            </div>

            <div id="paymentMethods" class="mt-4">
                <h5 class="mb-4 ms-4 text-lg-start text-center">Please select the desired payment method to conclude purchase</h5>
                <ul class="list-group list-group-flush px-lg-5 text-center">
                    <li class="list-group-item">
                        <button type="submit" class="btn btn-success" name="finish" value="Balance">Pay with account balance</button>
                    </li>
                    <li class="list-group-item d-block text-center">
                        <p class="mb-0"><a href="https://www.paypal.com/webapps/mpp/paypal-popup" title="How PayPal Works" onclick="javascript:window.open('https://www.paypal.com/webapps/mpp/paypal-popup','WIPaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700'); return false;"><img src="https://www.paypalobjects.com/webstatic/mktg/logo/AM_SbyPP_mc_vs_dc_ae.jpg" alt="PayPal Acceptance Mark"></a></p>
                        <button type="submit" class="btn btn-warning" name="finish" value="Paypal">Pay with <b>PayPal</b></button>
                        <p class="mt-1"><small>You will be redirected to the PayPal website to continue</small></p>
                    </li>
                </ul>
            </div>
        </div>
        <footer class="text-end mt-5 row">
            <button type="button" class="btn btn-dark col-lg-3 col-12 go_back_checkout" id="go_back_payment"><i class="bi bi-arrow-left-circle"></i> Go Back</button>
            {{-- <button type="button" class="btn btn-success offset-lg-6 col-lg-3 col-12">Finish and Pay</button> --}}
        </footer>
    </form>
</div>