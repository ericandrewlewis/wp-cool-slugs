# Cool Slugs

## WARNING: This plugin depends on the functionality exposed by [#18877](https://core.trac.wordpress.org/ticket/18877).  Please apply the latest patch there first.

A proof-of-concept WordPress plugin for ensuring URLs are unique, avoiding collisions.

This plugin is intended for WordPress core contributors to consider ways we can improve `wp_unique_post_slug()` internally. It is not intended for production use.

WordPress' algorithm for ensuring slug uniqueness could use some help.

* If posts and pages share the same permalink structure (e.g. `example.com/%postname%/`), a post and a page can have colliding slugs (see [#13459](https://core.trac.wordpress.org/ticket/13459)).
* If there's a static blog prefix (e.g. `example.com/blog/%postname%/`), a page's slug can collide with a blog post.

## How it works

The plugin uses WordPress' internal rewriting API to check if a desired URL is occupied.

This would fix the previously mentioned issues as well as these other cases

* A page with the desired slug `/category/uncategorized/` would collide with the Uncategorized category term archive, and assume the slug `/category/uncategorized-2/`.
* A page with the desired slug `book` would collide with a custom post type archive for "book," and assume the slug `/book-2/`.
* Any other conflict with an existing rewrite rule.
