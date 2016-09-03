<?php
/**
 * Give PDF Receipt Template
 *
 * Receipt - 4
 */
?>
<div style="background-color:#efefef;color:#333;padding-top:20px;font-family:Helvetica;width:100%; height:100%; margin:0; padding:0;">
	<div style="width:616px;margin:0 auto;padding:15px 0 0;font-size:14px;position:relative;height: 30px;">
		<div style="font-style:italic;color:#777;width:100%;text-align:center;">Donation Receipt: Your Tagline Here</div>
	</div>
	<div style="width:616px;background-color:#fff;margin:10px auto 0 auto;border:1px solid #e5e5e5;border-radius:8px">
		<div style="text-align:center">
			<div style="background-color:#e5e5e5; padding: 40px 0;"><img src="%assets_url%/images/logo_placeholder.png">
			</div>
		</div>
		<div style="margin: 35px 35px 20px;">
			<div style="font-size:18px">Dear {first_name},
				<div style="margin-top:20px">Your donation for {donation_name} via <a href="" style="color:#51aef0;text-decoration:none">http://mywebsite.org</a> on {date} is greatly appreciated. This is an official tax receipt issued by Organization Name - a non-profit organization.</div>
			</div>
			<div style="margin-top:15px">
				<div><strong>INVOICE TO:</strong></div>
				<div>{full_name}</div>
				<div>{billing_address}</div>
			</div>
			<div style="font-size:20px;font-weight:700;margin:18px 0;">Receipt of Charitable Donation:</div>
		</div>
		<div style="background-color:#f8f8f8;border-top: 1px solid #E6E6E6;border-bottom: 1px solid #E6E6E6;padding:28px 35px;line-height:23px">
			<div>
				<div><strong>DONATION NAME:</strong> {donation_name}</div>
				<div><strong>DONATION AMOUNT:</strong> {price}</div>
				<div><strong>PAYMENT METHOD:</strong> {payment_method}</div>
				<div><strong>DONATION STATUS:</strong> {payment_status}</div>
				<div><strong>DONATION DATE:</strong> {date}</div>
			</div>
			<div style="margin-top:20px">
				<div><strong>PAYMENT ID:</strong> {payment_id}</div>
				<div><strong>TRANSACTION ID:</strong> {transaction_id}</div>
				<div><strong>TRANSACTION KEY:</strong> {transaction_key}</div>
				<div><strong>DONATION STATUS:</strong> {payment_status}</div>
				<div><strong>PAYMENT METHOD:</strong> {payment_method}</div>
			</div>
		</div>
		<div style="padding:25px 35px 35px; margin:0;">Cepit pro zonae fixo quia iunctarum. Triones deus circumdare siccis fulgura animalia. Septemque sine parte. Melioris habitabilis naturae mundi campoque persidaque pontus bracchia terram. Fronde flamma.</div>
	</div>
	<div style="text-align:center;padding:26px 0">
		<div>123 Street Address</div>
		<div>City, State, Zip</div>
		<div>Tel: 1.888.555.5555</div>
		<div style="margin-top:15px">
			<div style="font-weight:700">Website:</div>
			<div><a href="http://mywebsite.org" style="color:#aeaeae">http://mywebsite.org</a></div>
		</div>
		<div style="margin-top:15px">
			<div style="font-weight:700">Email:</div>
			<div><a href="mailto:email@hotmail.com" style="color:#aeaeae">email@hotmail.com</a></div>
		</div>
	</div>
</div>