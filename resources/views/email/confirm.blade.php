<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title></title>

</head>


<style type="text/css">
	.signature{
		border-radius: 10px;
    	background-color: aliceblue;
    	padding: 10px;
	}

	.main-message{
		border-radius: 10px;
    	background-color: aliceblue;
    	padding: 20px;
    	margin-bottom: 10px;
	}

	h1, h3, h2, a, p{
		font-family: sans-serif;
	}

</style>

<body>

<div class="main-message">
	<h2 class="text">Dobrodošli v Dovidigom trgovini!</h2>
	<p class="text">Kliknite na spodnjo povezavo za aktivacijo uporabniškega računa.</p>

	<p></p>


	<a href='{{ $confirmation }}'>Aktivacija ra&#269;una</a>

	<br>
	<p>Za tehnično podporo pišite na naslov <a href="mailto:support@dovidigom.si">support@dovidigom.com</a></p>
</div>
<div class="signature">
	<p>
	Dovidigom
	<br>
	Mal dnarja pa velik muske.
	</p>
</div>


</body>
</html>