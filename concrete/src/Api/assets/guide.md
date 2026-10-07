# The API of this Concrete CMS site

The site calls itself "%siteName%" and runs on Concrete CMS %concreteVersion%.
Its REST API answers at %apiBaseUrl%

Which endpoints there are, what they take and what they answer is in the OpenAPI specification of this
installation, which `GET %openApiSpecificationUrl%` serves to a token that has the `system:openapi:read`
scope.
This guide only says what that specification can't say.

## Answers

A failure comes back with a 4xx status and `{"error": true, "errors": ["What went wrong"]}`, which the
specification doesn't describe.
Calling an endpoint that your token has no scope for is one of those failures, and it answers 400 as a
malformed request would: when a 400 says that the endpoint is out of scope, the request is fine and the
token isn't, and only the administrators of this site can widen the scopes of a client.

The `includes` query parameter asks for the objects related to the ones being read, and the specification
lists the values that each endpoint takes: `GET /pages/123?includes=areas,custom_attributes` answers with
the areas and the attributes of that page as well.
Attributes are read that way, while they are written as an `attributes` object keyed by attribute handle.

A list that takes an `after` parameter is walked with a cursor: pass as `after` the `meta.cursor.next`
of the answer you got, until no object comes back.

## Pages are made of versions

A page keeps every version of its contents: a visitor is served the approved one, while a user who may
edit that page is served the most recent one, draft and all.
Reads of this API serve the approved one, which is what a visitor sees: the `version` of a page answer
tells which one you are looking at.
A read of a single page takes `?version=recent`, which serves the most recent one instead, draft and
all, and only to whoever may see the versions of that page.

Editing a page that has no draft yet opens one: the change lands in a new, unapproved version, and the
site keeps serving the previous one until that version is approved, so what you have just written
comes back only with `?version=recent`.
Further changes keep landing in that same draft, so a client that edits and then approves publishes all
of them at once.
Approving is what publishes, so a draft nobody approves waits out of sight however long, and whoever
approves it in the end publishes what anybody else had left in it.

## Adding a page

Adding a page takes the handle of a page type and the handle of a page template: `GET /page_types` lists
the types, each with the templates it allows, and `GET /page_templates` names every template.

The page that comes out is an exception to the versions above: its first version is approved as it is
created, so the page is public right away, empty.

## Deleting a page

Deleting a page doesn't wipe it: unless the administrators of this site turned the trash off, the page
goes there, out of sight but still around.
Where a workflow guards the deletion, the answer only means that the request was filed.
The home page of a site is never deleted, and the answer says it was all the same.

## The address of a page

A page is served at its `path`, appended to the site root: a page whose `path` is `/about/us` answers at
%siteRootUrl%about/us
Take that root as it is, instead of building one out of the host: it already carries whatever this
installation puts before the path of a page, such as the `index.php/` that it needs while it isn't
rewriting its URLs.
Nothing answers at an address such as `/pages/123`: that is an endpoint of this API, not an address of
this site.
The home page is the one without a path of its own, `null`, and it answers at the site root itself.
The last step of that path is the `url_slug` of the page, which a client can choose when it adds or
updates one: left out, it is made out of the name of the page.

A page that has never been published is a different matter: its `path` is `/!drafts/` followed by its own
ID, so it answers at %siteRootUrl%!drafts/123, and only to a user who may see the versions of that page.
To anybody else the site answers 404, as it does for a page that isn't active.

## Areas and blocks

A page holds areas, an area holds blocks, and the blocks are the contents of the page.
Some block types hold areas of their own, so the chain goes on: the columns of a `core_area_layout`
block are areas, and they hold blocks like any other.
A `core_container` block shows a container, a piece of layout that brings areas of its own, and those
areas hold blocks just the same: `GET /containers` lists the containers, and every theme of
`GET /page_themes` carries the ones it can show.
The areas of a page answer carry the ones that live inside blocks too, under the handle that nests them
(`Main : 7 : Column 2`), and an inner area nothing renders any more keeps its blocks in the version: the
page stops showing them, which is what the web interface calls orphaned blocks.
Which areas a page has is up to the theme it is shown with: the theme declares them and names them, and
that name is the handle an area is identified by.
The `theme` of a page answer names the theme it is shown with: the one of the page, when it carries one,
and the one of its site otherwise, which `GET /sites` gives as `defaults.page_theme`.
A client can give a page a theme of its own, and `GET /page_themes` lists the ones of this installation,
each with the grid framework its layouts are sized on.
A theme can come in skins, which are the same pages in other colours: the `theme_skin` of a page names
the one it is shown with, and stays empty where the theme offers none.
Sending it empty hands the page back to the skin of its site, and giving a page a theme that hasn't the
skin the page carries drops that skin instead of failing.
An area handle is a plain name and not a slug, capitals and spaces included, so it travels URL-encoded in
the path of a request (`Page%20Footer`), and the names themselves are a convention rather than a rule:
themes usually call `Main` the area that holds the body of a page, and `Page Header`, `Page Footer` and
`Sidebar` the other usual ones.
An area of a page is a record that whatever drew it created, so a page that nothing has drawn yet has
none, and a page whose theme or template changed still carries the areas the old one made.
A page hands over every area it has, the empty ones included; `GET /pages/123?includes=areas&areas=with_blocks`
asks for the areas holding a block instead.
Adding a block to an area handle that the page has never used creates that area anyway: the block is
saved, but a theme that declares no such area never shows it.

The fields of the `value` of a block depend on its block type, and `GET /block_types` answers with every
block type of this installation, each with the `value_schema` of the value its blocks take and give back.
A block type that declares no schema of its own is marked `x-concrete-undescribed`, and the only way to
learn its fields is to read a block of that type.

To lay blocks side by side, add a block of the `core_area_layout` type: it holds columns of its own, and
the value it gives back names the area of each of them, for the areas endpoints to fill.
The area that holds the layout is none the wiser, and keeps holding its other blocks as before.
A layout can also be made out of a ready-made one, and `GET /layout_presets` lists them: the ones whose
`theme` is empty were defined in this installation and fit any page, while the others only fit the pages
shown with the theme they name.

## Stacks

A stack is a set of blocks that a page shows wherever a `core_stack_display` block puts it, and
`GET /stacks` lists the ones of this installation.
A stack answers with the ID of the page that holds its blocks: the areas endpoints work on that page,
in its `Main` area, so filling a stack is filling an area like any other.
A stack of a site that speaks several languages answers with a version of itself per language, under
`localized`, each the page of its own blocks.
The `id` of the stack is still the one to hand to a `core_stack_display` block whatever the language of
the page it sits in: the site shows the version of the language that page speaks, and the stack itself
where that language has none.
The blocks of a stack and of its localized versions travel with `?include_contents=true`, where the
request is allowed to read them.

## Files

The identifier of a file is its UUID, whatever the specification calls that parameter: a numeric ID
answers only for the files added before UUIDs existed, which have none.

## This installation

These are the sites served by this installation, each with the root that the paths of its pages are
appended to:

%sites%

`GET /sites` tells more about each of them, and above all the locales it is written in: a locale says
the path its pages hang from, `/` for the default one and something like `/it` for the others, so the
language of a page is the locale whose path the path of the page begins with.
