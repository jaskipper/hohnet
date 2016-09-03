<?php
/**
 * Give PDF Receipt Template
 *
 * Receipt - 1
 */
?>
<div style="background-color:#ededed;color:#333;font-family:Helvetica; width:100%; height:100%; margin:0; padding:0;">
	<div style="background-color:#4d5a6b;color:#fff;text-align:center;height:50px;line-height:50px;font-style:italic;font-size:14px">
		Your tagline here
	</div>
	<div style="margin-top:26px;text-align:center"><img src="%assets_url%/images/logo_placeholder.png"></div>
	<div style="width:614px;border: 1px solid #DEDEDE;margin:26px auto;background-color:#fff;text-align:center;padding-bottom:25px">
		<div>
			<div style="font-size:24px;font-weight:700;padding:20px 0">Organization Name</div>
			<div style="font-style:italic;margin-bottom:25px">
				<div>123 Street Address</div>
				<div>City, State, Zip</div>
				<div>Tel: 1.888.555.5555</div>
			</div>
			<div style="font-weight:700;margin-bottom:25px">
				<div>Website: <a href="" style="color:#51aef0;text-decoration:none">http://mywebsite.org</a></div>
				<div>Email: <a href="" style="color:#51aef0;text-decoration:none">email@hotmail.com</a></div>
			</div>
		</div>
		<div style="margin:0;border-bottom: 1px solid #E2E2E2;background-color:#f8f8f8">
			<div style="background-color:#4d5a6b;color:#fff;font-size:20px;font-weight:700;height:45px;line-height:35px">
				<span>Receipt of Charitable Donation</span></div>
			<div style="padding:25px 20px 35px 20px">
				<div>This is an official tax receipt issued by *Organization Name*</div>
				<div>- a non-profit agency located in the *Country Name*.</div>
				<div style="margin-top:20px;text-align:left;font-size:20px;padding:0 25px">
					<div style="min-height:72px">
						<div style="font-weight:700">Invoice to:</div>
						<div>{full_name}</div>
						<div>{billing_address}</div>
					</div>
					<div style="margin:15px 0">
						<div><span style="font-weight:700">Payment ID:</span> {payment_id}</div>
						<div><span style="font-weight:700">Transaction Key:</span> {transaction_key}</div>
						<div><span style="font-weight:700">Transaction ID:</span> {transaction_id}</div>
						<div><span style="font-weight:700">Donation Status:</span> {payment_status}</div>
						<div><span style="font-weight:700">Payment Method:</span> {payment_method}</div>
					</div>
					<div><span style="font-weight:700">Donation Name:</span> {donation_name}</div>
					<div><span style="font-weight:700">Donation Amount:</span> {price}</div>
					<div><span style="font-weight:700">Payment Method:</span> {payment_method}</div>
					<div><span style="font-weight:700">Donation Status:</span> {payment_status}</div>
					<div><span style="font-weight:700">Donation Date:</span> {date}</div>
				</div>
			</div>
		</div>
		<div style="margin:15px 66px 0 66px;text-align:justify">Seductaque tepescunt nullo fuit. Obliquis motura
			circumfluus. Omnia terram pace nunc. Militis arce mortales et. Sui regio vindice caelo sui! Tuti natura capacius illic turba obliquis orbem ab deorum! Naturae colebat locis sinistra pronaque semina! Ubi duae
		</div>
	</div>
	<div style="width:614px;margin:0 auto;text-align:center;font-style:italic;font-size:14px;padding-bottom:45px">
		<div>Seductaque tepescunt nullo fuit. Obliquis motura circumfluus. Omnia terram pace nunc. Militis arce mortales et. Sui regio vindice caelo sui! Tuti natura capacius illic turba obliquis orbem ab deorum! Naturae colebat locis sinistraz</div>
	</div>
</div>
