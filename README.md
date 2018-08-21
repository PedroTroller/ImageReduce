# Image optimization

## Run with Docker

```bash
 $ docker run --rm -v $(pwd)/app pedrotroller/image-reduce ./**
```


 - `-v $(pwd)/app` to make your sources accessible by the application.
 - `./**` the location of files to analyze

## Run with PHP

This project is just a wrapper of the [spatie/image-optimizer](https://github.com/spatie/image-optimizer) library.
