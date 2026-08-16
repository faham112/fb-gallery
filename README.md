# FB-Gallery

Stylish WordPress image gallery with random domain redirect.

**Made by ❤️ coded by Faheem Badshah**

## Features

- Auto-creates a page on activation ("FB Gallery")
- Set it as Homepage from **Settings → Reading**
- Manage up to 5 redirect domains
- Add images by URL or upload
- 3 second auto-redirect + click to redirect
- Back button returns to gallery
- Custom login URL: `/login.php`
- Fully mobile responsive
- GitHub auto-updates via CI/CD

## Installation

1. Download the latest **Release ZIP** from GitHub Releases
2. WordPress → Plugins → Add New → Upload Plugin
3. Activate the plugin
4. Go to **Settings → Reading** → set **FB Gallery** as Homepage
5. Open **FB Gallery** menu in admin to manage domains & images

## Login URL

```
https://yourdomain.com/login.php
```

## CI/CD – How to release

1. Update version in `fb-gallery.php` (e.g. `1.0.3`)
2. Commit and push to `main`
3. Create and push a tag:

```bash
git tag v1.0.3
git push origin v1.0.3
```

4. GitHub Actions will automatically:
   - Build the complete plugin ZIP
   - Create a Release
   - Attach `fb-gallery.zip` as asset

WordPress sites will detect the update.

## Author

- Faheem Badshah
- WhatsApp: [+92 301 3250144](https://wa.me/923013250144)
- GitHub: [faham112](https://github.com/faham112)

## License

GPL-2.0+
