<?php
/**
 * Give PDF Receipt Template
 *
 * Receipt - 2
 */
?>
<div style="background-color:#3d3d3d;color:#333;padding:50px 0 42px 0;font-family:Times-Roman;width:100%;height:100%;margin:0;padding:0;">
	<div style="font-style:italic;width:616px;text-align:center;padding: 15px 0 20px;margin: 0 auto;color: #FFF;">Your tagline here</div>
	<div style="width:616px;background-color:#fff;padding:0;margin:0 auto 30px;border-radius:8px;position:relative;">
		<div style="padding: 35px;margin: 0;"><img src="%assets_url%/images/logo_placeholder.png"></div>

		<div style="margin:0; padding:0 15px 0 35px;font-size:18px">Dear {full_name},
			<div style="margin-top:15px">Your donation for {donation_name} on
				<a href="http://website.com">http://website.com</a> on {date} is greatly appreciated. This is an official tax receipt issued by Organization Name - a non-profit organization.
			</div>
		</div>
		<div style="text-align:center;font-size:21px;font-weight:700;margin:25px 0;padding:0;">Receipt of Charitable Donation</div>
		<div style="background-color:#f8f8f8;padding:30px 35px;position:relative;line-height:23px">
			<div>
				<div><strong>DONATION NAME:</strong> {donation_name}</div>
				<div><strong>DONATION AMOUNT:</strong> {price}</div>
				<div><strong>PAYMENT METHOD:</strong> {payment_method}</div>
				<div><strong>DONATION STATUS:</strong> {payment_status}</div>
				<div><strong>DONATION DATE:</strong> {date}</div>
			</div>
			<div style="position:absolute;left:380px;top:30px;width:160px">
				<div><strong>INVOICE TO:</strong></div>
				<div>{transaction_id}</div>
				<div>{billing_address}</div>
			</div>
			<div style="margin-top:35px">
				<div><strong>PAYMENT ID:</strong> {payment_id}</div>
				<div><strong>TRANSACTION KEY:</strong> {transaction_key}</div>
				<div><strong>DONATION STATUS:</strong> {payment_status}</div>
				<div><strong>PAYMENT METHOD:</strong> {payment_method}</div>
			</div>
		</div>
		<div style="padding:25px 35px 35px;margin:0;font-size:18px;">
			<div>Cepit pro zonae fixo quia iunctarum. Triones deus circumdare siccis fulgura animalia. Septemque sine parte. Melioris habitabilis naturae mundi campoque persidaque pontus bracchia terram. Fronde flamma.</div>
			<div style="padding: 20px 0;margin:0;">Sincerely,
				<div><img style="display:block;margin-top: 10px;" src="%assets_url%/images/signature_placeholder.png">
				</div>
				<div style="margin-top:5px">B. FRANKLIN
					<div>President, Organization Name</div>
				</div>
			</div>
		</div>
	</div>
	<div style="color:#fff;text-align:center;margin-top:20px;font-size:14px">
		<div style="font-style:italic">
			<div>123 Street Address</div>
			<div>City, State, Zip</div>
			<div>Tel: 1.888.555.5555</div>
		</div>
		<div style="font-weight:700;margin-top:8px">Website:
			<div><a href="http://mywebsite.org" style="color:#aeaeae;text-decoration:none">http://mywebsite.org</a>
			</div>
		</div>
		<div style="font-weight:700;margin-top:8px; margin-bottom:30px">Email:
			<div><a href="mailto:email@hotmail.com" style="color:#aeaeae;text-decoration:none">email@hotmail.com</a>
			</div>
		</div>
	</div>
</div>
