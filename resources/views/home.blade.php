<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Laravel PHP Framework</title>
		<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css" />
		<link href="assets/css/app.css" media="all" rel="stylesheet" type="text/css" />
	</head>

	<body>
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">Laravel CI</a>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-3 col-md-2 sidebar" id="projects">

                </div>

                <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                    <div class="table-responsive" id="table-container">
                    </div>
                </div>
            </div>
        </div>

		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.js" type="text/javascript"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/react/0.12.0/react-with-addons.js" type="text/javascript"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/react/0.12.0/JSXTransformer.js" type="text/javascript"></script>
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js" type="text/javascript"></script>

		<script type="text/jsx">
			@include('javascripts.reactjs-app')
		</script>
	</body>
</html>
