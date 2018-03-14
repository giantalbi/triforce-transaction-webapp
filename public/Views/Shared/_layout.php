<!DOCTYPE html>

<!--
               /\
              /  \
             /    \
            /      \
           /        \
          /__________\
         /\__________/\
        /  \        /  \
       /    \      /    \
      /      \    /      \
     /        \  /        \
    /__________\/__________\
    \__________/\__________/
-->

<html>
    <head>
        <meta charset="utf-8">
        <title>Triforce</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script src="https://code.jquery.com/jquery-3.2.1.min.js"  crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

        <link rel="stylesheet" type="text/css" href="/public/css/home-style.css">
        <link rel="icon" type="image/png" href="/public/img/logo.png" />
        <script type="text/javascript" src="/public/js/home-index.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-dark" style="background-color:#2b803e;">
            <span class="navbar-brand mb-0 h1">Triforce</span>
        </nav>
        <div class="container" style="padding-top: 10px;padding-bottom: 10px;">
            <div class='row justify-content-md-center justify-content-sm-center justify-content-center'>
                <!--Partial View-->
                <?php echo $partial_content;?>
            </div>
        </div>
    </body>
</html>
