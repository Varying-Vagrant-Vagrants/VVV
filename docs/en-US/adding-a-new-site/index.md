# Adding a New Site

Adding a new site is as simple as adding it under the sites section of `vvv-custom.yml`.

To add a site:

```YAML
	example:
```

To add a site with a repository:

```YAML
	example: https://github.com/example/example.git
```
To add a site with a repository and a domain/host:

```YAML
example:
  repo: https://github.com/example/example.git
  hosts:
  - example.local
```
