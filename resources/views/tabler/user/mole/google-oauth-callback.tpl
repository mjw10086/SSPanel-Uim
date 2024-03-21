<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Oauth2 callback</title>
</head>

<body>
    <script>
        function sendMessageToParent() {
            window.opener.postMessage({$message}, "*");
            window.close();
        }

        window.onload = function() {
            sendMessageToParent();
        };
    </script>
</body>

</html>