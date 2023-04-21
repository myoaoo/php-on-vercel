# PHP on Vercel ▴

This is a repository I've created to test if we can get traditional PHP file-based
routing working on the [community Vercel Functions PHP runtime](https://github.com/vercel-community/php).

See instructions below on how.

# Instructions

1. Create a directory `api` and place all your PHP files in there.

2. Create a file called `vercel.json` at the root of your project folder with the following content:

```json
{
  "functions": {
    "api/*.php": {
      "runtime": "vercel-php@0.6.0"
    }
  },
  "routes": [{ "src": "/(.*)", "dest": "/api/$1" }]
}
```

Note the example given here uses version 0.6.0 of the runtime which corresponds to PHP 8.2.x,
but other versions may also be used.

3. Push to GitHub and create your project on Vercel.
