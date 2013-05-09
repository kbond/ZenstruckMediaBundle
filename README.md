# ZenstruckMediaBundle

**NOTE:** This bundle is under heavy development, **use at your own risk**

Provides a simple media/file management GUI for Symfony2:

* Integration with CKEditor
* Media form type with browse server

[![Screenshot][1]][2]

[View Demo][2]

## Full Default Config

```yaml
zenstruck_media:
    default_layout:       "ZenstruckMediaBundle:Twitter:default_layout.html.twig"
    media_form_type:      false
    role_permissions:     false
    filesystems:          # Required

        # Prototype
        name:
            root_dir:                  "%kernel.root_dir%/../web/files" # Required
            web_prefix:                "/files" # Required
            filesystem_class:          "Zenstruck\MediaBundle\Media\Filesystem"
            filesystem_manager_class:  "Zenstruck\MediaBundle\Media\FilesystemManager"
```

[1]: https://lh5.googleusercontent.com/-c7FHKPXsrvg/UYuZtMA3pKI/AAAAAAAAKGA/82ZdM0Tpr4Y/w963-h438-no/zenstruck-media.png
[2]: http://sandbox.zenstruck.com/