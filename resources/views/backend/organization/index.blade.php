<form method="POST" action="https://rc-checkout.esewa.com.np/api/client/intent/payment/book">

    @csrf

    <input type="hidden" name="product_code" value="INTENT">
    <input type="hidden" name="amount" value="100">

    <input type="hidden" name="transaction_uuid" value="txn-20260610-768ce97e-4ec0-42db-bc77-8839a76b6817">

    <input type="hidden" name="signed_field_names" value="product_code,amount,transaction_uuid">

    <input type="hidden" name="signature" value="/F5+UkG/iBlRrVj0IKqUE+sPWYwQLr+KPAzyB5JnQbI=">

    <input type="hidden" name="callback_url" value="{{ url('/esewa/callback') }}">
    <input type="hidden" name="redirect_url" value="{{ url('/esewa/redirect') }}">

    <input type="hidden" name="properties[customer_id]" value="CUST12345">
    <input type="hidden" name="properties[remarks]" value="Internet bill payment">

    <button type="submit">Pay with eSewa</button>
</form>
