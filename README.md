# Example Neucore plugin

_Needs [Neucore](https://github.com/tkhamez/neucore) version 3.3.0 or higher._

An example plugin with its own frontend that shows how to make ESI requests via Neucore 
and implements a CLI command.

The content of the frontend directory must be deployed to `web/plugin/example`.

## Development:

```shell
docker build --tag neucore-example-plugin .
docker run -it --mount type=bind,source="$(pwd)",target=/app --workdir /app neucore-example-plugin /bin/sh
```
