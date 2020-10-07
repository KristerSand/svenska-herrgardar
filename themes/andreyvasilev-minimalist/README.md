#### This is an OctoberCMS Minimalist theme.

### Documentation

To get started, go to the theme directory and run
```
npm i
npm run watch
```

Links to documentation to help you get started!

* https://octobercms.com/docs/themes/development
* https://octobercms.com/docs/cms/themes
* https://laravel.com/docs/5.6/mix
* https://webpack.js.org

You can easily install the necessary frameworks and packages using **npm** and use them in your theme.

###Example:
Use Bootstarp 4.
```
npm i popper.js bootstrap --save
```

####assets/js/src/app.js
```
window.Popper = require('popper.js').default;
require('bootstrap');
```

####assets/css/src/app.scss

```
@import "~bootstrap/scss/bootstrap";
```