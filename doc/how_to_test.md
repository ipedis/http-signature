## How to test

##### Run local Web server

```
  cd demo
  php -S localhost:5000
```

##### Use the different commands to send http request to the local web server

- Sending a GET request

```
  cd demo
  php callWebhook.php get
```

- Sending a POST request

```
  cd demo
  php callWebhook.php post
```

- Sending an unsigned POST request

```
  cd demo
  php callWebhook.php invalid-post
```

- Sending an DELETE request

```
  cd demo
  php callWebhook.php delete
```

- Sending an expired GET request

```
  cd demo
  php callWebhook.php expired
```
