<?php
/**
 * Give PDF Receipt Template
 *
 * Receipt - 3
 */
?>
<div style="background-color:#fff;color:#333;padding-top:25px;font-family:Times-Roman; width:100%; height:100%; margin:0; padding:0;">
	<div style="width:616px;background-color:#fff;margin:30px auto;">
		<div style="text-align:center">
			<div style="margin:0 auto"><img src="%assets_url%/images/logo_placeholder.png"></div>
			<div style="font-style:italic;margin:10px 0">Your tagline here</div>
			<div style="font-weight:700;font-size:20px">Thank you for your donation!</div>
		</div>
		<div style="text-align:center;font-size:18px;margin-top:10px">Your donation for {donation_name} on
			<a href="http://website.com">http://website.com</a> on {date} is greatly appreciated. This is an official tax receipt issued by Organization Name - a non-profit organization.
		</div>
		<div style="width:408px;margin:0 auto">
			<div style="background-color:#525252;color:#fff;height:50px;line-height:50px;text-align:center;font-size:20px;font-weight:700;margin-top:20px">
				Receipt of Charitable Donation
			</div>
			<div style="background-color:#f8f8f8;padding:20px 35px;line-height:23px;border: 1px solid #E5E5E5;">
				<div style="text-align:center">
					<div><strong>INVOICE TO:</strong></div>
					<div>{transaction_id}</div>
					<div>{billing_address}</div>
				</div>
				<div style="margin-top:25px">
					<div><strong>DONATION NAME:</strong> {donation_name}</div>
					<div><strong>DONATION AMOUNT:</strong> {price}</div>
					<div><strong>PAYMENT METHOD:</strong> {payment_method}</div>
					<div><strong>DONATION STATUS:</strong> {payment_status}</div>
					<div><strong>DONATION DATE:</strong> {date}</div>
				</div>
				<div style="margin-top:25px">
					<div><strong>PAYMENT ID:</strong> {payment_id}</div>
					<div><strong>TRANSACTION KEY:</strong> {transaction_key}</div>
					<div><strong>DONATION STATUS:</strong> {payment_status}</div>
					<div><strong>PAYMENT METHOD:</strong> {payment_method}</div>
				</div>
			</div>
		</div>
		<div style="margin-left:35px;margin-top:20px;font-size:18px">
			<div>Cepit pro zonae fixo quia iunctarum. Triones deus circumdare siccis fulgura animalia. Septemque sine parte. Melioris habitabilis naturae mundi campoque persidaque pontus bracchia terram. Fronde flamma.</div>
			<div style="margin:15px 0 0">Sincerely,
				<div><img style="display:block;margin:15px 0" src="%assets_url%/images/signature_placeholder.png"></div>
				<div>B. FRANKLIN<br>
					<div>President, Organization Name</div>
				</div>
			</div>
		</div>
	</div>
	<div style="background-color:#525252;color:#fff;text-align:center;margin-top:20px;padding:19px 0">
		<div style="font-style:italic">
			<div>123 Street Address</div>
			<div>City, State, Zip</div>
			<div>Tel: 1.888.555.5555</div>
		</div>
		<br>
		<div style="font-weight:700">
			<div>Website:
				<a href="http://mywebsite.org" style="color:#aeaeae;text-decoration:none">http://mywebsite.org</a></div>
			<div>Email:
				<a href="mailto:email@hotmail.com" style="color:#aeaeae;text-decoration:none">email@hotmail.com</a>
			</div>
		</div>
	</div>
</div>
