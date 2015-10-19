# ZenstruckMediaBundle

**NOTE:** This bundle is under heavy development, **use at your own risk**

Provides a simple media/file management GUI for Symfony2:

* Integration with CKEditor
* Media form type with browse server

[![Screenshot][1]][2]

[View Example Source Code][2]

## Installation

1. Add to your `composer.json`:

        composer require zenstruck/media-bundle

2. Download and install the AngularJS module [ngUpload][3] to your `web/vendor` folder. I suggest using
   [Bower][4]:

        bower install ngUpload

3. *Optional*  If using the slugify filename feature, add [cocur/slugify][5] to your composer.json

        composer require cocur/slugify

4. Register the bundle with Symfony2:

    ```php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Zenstruck\MediaBundle\ZenstruckMediaBundle(),

            // enable if you want to use the slugify filename feature
            // new Cocur\Slugify\Bridge\Symfony\CocurSlugifyBundle()
        );
        // ...
    }
    ```

## Full Default Config

```yaml
zenstruck_media:
    default_layout:       ZenstruckMediaBundle:Twitter:default_layout.html.twig
    slugify_filename_filter:  false
    filesystem_class:     Zenstruck\MediaBundle\Media\Filesystem
    media_form_type:      false
    role_permissions:     false
    filesystems:          # Required

        # Prototype
        name:
            root_dir:             %kernel.root_dir%/../web/files # Required
            web_prefix:           /files # Required
            secure:               false # set true and change the path to a non public path for secure file downloads

            # Comma separated list of extensions
            allowed_extensions:   ~ # Example: jpg,gif,png
```

[1]: https://lh5.googleusercontent.com/-c7FHKPXsrvg/UYuZtMA3pKI/AAAAAAAAKGA/82ZdM0Tpr4Y/w963-h438-no/zenstruck-media.png
[2]: https://github.com/kbond/sandbox
[3]: http://twilson63.github.io/ngUpload/
[4]: http://bower.io/
[5]: https://github.com/cocur/slugify#symfony2
