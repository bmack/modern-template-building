# "Modern Template Building" - A homage to TYPO3 templating back in the 90's

Before there was TemplaVoila or later Fluid-based templating, the creator of TYPO3 - Kasper Skaarhoj -
created a guide called "Modern Template Building" - where integrators could
write HTML templates with placeholders - so-called "Markers" and "Subparts" without having to write
everything in TypoScript.

This concept was still available until TYPO3 v10, but is discouraged, as both TemplaVoila,
and Fluid offer a lot more ways to create templates in a more flexible way.

For all the nostalgic TYPO3 lovers, I've created this extension, which ships
with two "Content Objects" - `TEMPLATE` and `FILE` to use them in future TYPO3
versions.

This extension is a perfect example how templates that were created with this
approach 15 years ago, still run on TYPO3 v9, v10 or later without having to adopt
anything. TYPO3 is loved for its
* backwards-compatibility,
* upgrade path and
* flexibility

and this is what can be achieved with a simple extension.

## Installation

Just install this extension (extension key `modern_template_building`) from the TYPO3 Extension Repository,
or via `composer req bmack/modern-template-building`, and you're good to go.

Once the extension is installed, you can continue to use the cObjects `FILE` and `TEMPLATE`
in your custom TypoScript code.

## Configuration

This is taken from the original TYPO3 documentation and was slightly adapted for this extension.

### TEMPLATE Content Object

With an object of type `TEMPLATE` you can define a template (e.g. an HTML file) which
should be used as a basis for your whole website. Inside the template
file you can define markers, which later will be replaced with dynamic
content by TYPO3.

#### template (Data type: cObject)

This must be loaded with the template-code. Usually this is done
with a `FILE` cObject. If it is not loaded with code, the object returns nothing.

**Example:**

    page.10 = TEMPLATE
    page.10 {
       template = FILE
       template.file = fileadmin/template.html
    }

This will use the file fileadmin/template.html as template for your website.

#### subparts.[array] (Data type: array of cObjects)

This is an array of subpart-markers (case-sensitive).

A subpart is defined by **two** markers in the template. The markers
must be wrapped by "###" on both sides. You may insert the subpart-
markers inside HTML-comment-tags!

**Example:**

In the template there is the subpart "HELLO":

    <!-- start of subpart ###HELLO### -->
    This is the HTML code, that will be loaded in the register
    and will be replaced with the result...
    <!-- end ###HELLO### -->

The following TypoScript code now replaces the subpart "HELLO" with
the text given in "value":

    page.10.subparts {
      HELLO = TEXT
      HELLO.value = En subpart er blevet erstattet!
    }

> Note:
> Before the content objects of each subpart are generated, all subparts
 in the array are extracted and loaded into the register so that you
 can load them from there later on.
>
> The register-key for each subparts code is "SUBPART_[theSubpartkey]".
>
> In addition the current-value is loaded with the content of each
 subpart just before the cObject for the subpart is parsed. That makes
 it quite easy to load the subpart of the cObject (e.g.: ".current = 1")
> E.g. this subpart above has the register-key "SUBPART_HELLO".
>
> *This is valid ONLY if the property .nonCachedSubst is not set (see
 below)!*

#### relPathPrefix (Data type: string / properties)

Finds all relative references (e.g. to images or stylesheets) and
prefixes this value.

If you specify properties (uppercase) these will match HTML tags and
specify alternative paths for them. See example below.

If the property is named "style", it will set an alternative path for
the "url()" wrapper that may be in `<style>` sections.

**Example:**

    page.10 = TEMPLATE
    page.10 {
      template = FILE
      template.file = fileadmin/template.html
      relPathPrefix = fileadmin/
      relPathPrefix.IMG = fileadmin/img/
    }

In this example all relative paths found are prefixed with "fileadmin/"
unless it was the src attribute of an img tag in which case the path
is prefixed with "fileadmin/img/".

#### marks.[array] (Data type: array of cObjects)

This is an array of marks-markers (case-sensitive).

A mark is defined by **one** marker in the template. The marker must
be wrapped by "###" on both sides. Opposite to subparts, you may **not**
insert the subpart-markers inside HTML-comment-tags! (They will not be
removed.)

**Example:**

In the template:

    <div id="copyright">
      &copy; ###DATE###
    </div>

The following TypoScript code now dynamically replaces the marker
"DATE" with the current year:

    page.10.marks {
      DATE = TEXT
      DATE {
        stdWrap.data = date : U
        stdWrap.strftime = %Y
    }

Marks are substituted by a `str_replace`-function. The subparts loaded
in the register are also available to the cObjects of markers (only if
.nonCachedSubst is not set!).

#### wraps.[array] (Data type: array of cObjects)

This is an array of wraps-markers (case-sensitive).

This is shown best by an example:

**Example:**

In the template there is the subpart "MYLINK":

    This is <!--###MYLINK###-->a link to my<!--###MYLINK###--> page!

With the following TypoScript code the subpart will be substituted by
the wrap which is the content returned by the MYLINK cObject. :

    page.10.wraps {
        MYLINK = TEXT
        MYLINK.value = <a href="#"> | </a>
    }

#### workOnSubpart (Data type: string / stdWrap)

This is an optional definition of a subpart, that we decide to work
on. In other words; if you define this value that subpart is extracted
from the template and is the basis for this whole template object.


#### markerWrap (Data type: wrap / stdWrap)

Default: `### | ###`

This is the wrap the markers are wrapped with. The default value is
`### | ###` resulting in the markers to be presented as
`###[marker_key]###`.

Any whitespace around the wrap-items is stripped before they are set
around the `marker_key`.

#### substMarksSeparately (Data type: boolean / stdWrap)

If set, then marks are substituted in the content AFTER the
substitution of subparts and wraps.

Normally marks are not substituted inside of subparts and wraps when
you are using the default cached mode of the TEMPLATE cObject. That is
a problem if you have marks inside of subparts! But setting this flag
will make the marker-substitution a non-cached, subsequent process.

Another solution is to turn off caching, see below.

#### nonCachedSubst (Data type: boolean / stdWrap)

If set, then the substitution mode of this cObject is totally
different. Normally the raw template is read and divided into the
sections denoted by the marks, subparts and wraps keys. The good thing
is high speed, because this "pre-parsed" template is cached. The bad
thing is that templates that depend on incremental substitution (where
the order of substitution is important) will not work so well.

By setting this flag, markers are first substituted by `str_replace` in
the template - one by one. Then the subparts are substituted one by
one. And finally the wraps one by one.

Obviously you loose the ability to refer to other parts in the
template with the register-keys as described above.

#### stdWrap (Data type: stdWrap)

This cObject can be managed with a global stdWrap functionality as well.

### Examples

    page.10 = TEMPLATE
    page.10 {
      template = FILE
      template.file = fileadmin/test.tmpl
      subparts {
        HELLO = TEXT
        HELLO.value = This is the replaced subpart-code.
      }
      marks {
        Testmark = TEXT
        Testmark.value = This is replacing a simple marker in the HTML code.
      }
      workOnSubpart = DOCUMENT
    }

In this example a template named test.tmpl is loaded and used. The
subpart "HELLO" and the mark "Testmark" in the template file will be
replaced with the output of the according cObjects.


### FILE Content Object

This object returns the content of the file specified in the property "file".

> Note: Do not mix this up with the cObject :ref:`FILES <cobj-files>`; both are
different cObjects.

#### file (Data type: resource / stdWrap)

The file whose content should be returned.

If the resource is **jpg, jpeg, gif or png** the image is inserted as
an image-tag. All other formats are read and inserted into the HTML
code.

The maximum filesize of documents to be read is set to 1024 KB
internally!

#### linkWrap (Data type: linkWrap / stdWrap)

Executed before ".wrap" and ".stdWrap".

#### wrap (Data type: wrap / stdWrap)

Executed after ".linkWrap" and before ".stdWrap".


#### stdWrap (Data type: stdWrap)

This cObject can be managed with a global stdWrap functionality as well.

Executed after ".linkWrap" and ".wrap".


#### altText / titleText (Data type: string / stdWrap)

**For <img> output only!**

If no alttext is specified, it will use an empty alttext.


#### emptyTitleHandling (Data type: string / stdWrap)

Value can be `keepEmpty` to preserve an empty title attribute, or
`useAlt` to use the alt attribute instead. Defaults to `useAlt`.

#### longdescURL (Data type: string / stdWrap)

**For <img> output only!**

"longdesc" attribute (URL pointing to document with extensive details
about image).

### Examples

In this example a page is defined, but the content between the body-
tags comes directly from the file "gs.html":

    page = PAGE
    page.typeNum = 0
    page.10 = FILE
    page.10.file = fileadmin/gs/gs.html


## License

The extension is licensed under GPL v2+, same as the TYPO3 Core. For details see the LICENSE file in this repository.

## Open Issues

If you find an issue, feel free to create an issue on GitHub or a pull request.

## Credits

This extension was created by [Benni Mack](https://github.com/bmack) in 2020, the original credits go to the TYPO3
development team and contributors that have maintained this code for over 20 years until it was removed from Core.
