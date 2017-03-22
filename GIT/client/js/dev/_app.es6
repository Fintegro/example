module.exports = (body) => {
  let pages = require('./modules/pages.es6')
  let Events = require('./modules/events.es6')

  pages(body).page('home')
  Events(body).initApp()

  setTimeout(() => {
    body.animate({ opacity: 1 }, 250)
  }, 750)
}
