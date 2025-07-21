@extends('layouts.app')

@section('title', 'Stripe Debug')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Stripe Configuration Debug</h4>
                </div>
                <div class="card-body">
                    <h5>Environment Variables:</h5>
                    <ul>
                        <li>STRIPE_KEY: {{ env('STRIPE_KEY') ? 'Set (' . strlen(env('STRIPE_KEY')) . ' chars)' : 'Not set' }}</li>
                        <li>STRIPE_SECRET: {{ env('STRIPE_SECRET') ? 'Set (' . strlen(env('STRIPE_SECRET')) . ' chars)' : 'Not set' }}</li>
                    </ul>

                    <h5>Config Values:</h5>
                    <ul>
                        <li>services.stripe.key: {{ config('services.stripe.key') ? 'Set' : 'Not set' }}</li>
                        <li>services.stripe.secret: {{ config('services.stripe.secret') ? 'Set' : 'Not set' }}</li>
                    </ul>

                    <h5>Stripe Connection Test:</h5>
                    <div id="stripe-test">
                        <button onclick="testStripe()" class="btn btn-primary">Test Stripe Connection</button>
                        <div id="test-result" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testStripe() {
    const resultDiv = document.getElementById('test-result');
    resultDiv.innerHTML = '<div class="alert alert-info">Testing...</div>';
    
    fetch('/api/test-stripe')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <strong>✅ Success!</strong><br>
                        Account ID: ${data.account_id}<br>
                        Country: ${data.country}<br>
                        Currency: ${data.default_currency}
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>❌ Error:</strong><br>
                        ${data.error}
                    </div>
                `;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <strong>❌ Network Error:</strong><br>
                    ${error.message}
                </div>
            `;
        });
}
</script>
@endsection