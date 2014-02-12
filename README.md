Starship
========

A static site generator for PHP.

Basically a simpler Jekyll clone, with some PHP/Twig flavour. Written because
@msvrtan was not happy about using a Ruby tool for generating ZgPHP web pages.

Usage
-----

Create scaffolding for a new site:
```
starship init /path/to/site
```

To generate an existing site, in the site folder run:
```
starship build
```

To serve a site using the PHP dev server:
```
starship serve
```

Directory structure
-------------------

```
.
├─── _config.yml
├─── index.html
├─── _posts
│    ├─── 2014-01-01-my-first-post.md
│    └─── 2014-01-03-my-second-post.textile
│
├─── _drafts
│    └─── 2014-01-07-my-third-post-in-progress.md
│
├─── news
│    ├───  index.html
│    └─── _posts
│         ├─── 2013-10-19-breaking-news.md
│         └─── 2013-10-24-unbreaking-news.md
│
├─── _includes
│    ├─── footer.html
│    ├─── navigation.html
│    └─── sidebar.html
│
└─── _templates
     ├─── base.html
     └─── post.html
```
