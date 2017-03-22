import { _getData } from './api.es6'
import { checkAuth, getRowMediaPlugin } from './coreFunc.es6'

module.exports = (body) => {
  function _page (el) {
    require.ensure([], (require) => {
      let page = typeof el === 'object' ? el.data('page') : el
      let modulesArr
      let urlArr
      let id = typeof el === 'object' ? el.data('id') : false
      let wrapperArr
      let templateObj
      let callbackArr

      switch (page) {
        case 'home':
          modulesArr = ['profiles', 'photoSmall', 'posts', 'friends']
          urlArr = ['/profiles', '/album', '/posts', '/social-activities']
          id = id
          wrapperArr = [
            '#wrapper',
            '.user__info-photos [data-grid="images"]',
            '.wall-list',
            '.followers-list-block'
          ]
          templateObj = {
            view: [
              'partials/layout/index.hbs',
              'partials/albums/smallPhotosBlock.hbs',
              'partials/posts/simple-item.hbs',
              'partials/followers/followers-items.hbs'
            ],
            style: ['app-style.scss']
          }
          callbackArr = [
            () => {
              console.info('home')
            },
            getRowMediaPlugin(),
            () => {
              let animation = require('./animation.es6')
              let commentControll = require('./comments.es6')

              animation().wall(false)
              commentControll(body).events()
              getRowMediaPlugin()
            }
          ]
          break

        case 'news':
          modulesArr = ['news']
          urlArr = ['/news']
          id = id
          wrapperArr = ['.wall-list']
          templateObj = {
            view: ['partials/posts/demo-news.hbs']
          }
          callbackArr = [() => { console.log(1) }]
          console.info('news')
          break

        case 'profile':
          modulesArr = ['profiles', 'album']
          urlArr = ['/profiles', '/album']
          id = id
          wrapperArr = ['.l-index', '.albums']
          templateObj = {
            view: ['partials/layout/profile.hbs', 'partial/albums/imdex.hbs']
          }
          callbackArr = [
            () => {
              require('./../../../scss/layout/_profile.scss')
              let profileApi = require('./profile.es6')
              profileApi(body).Events()
            },
            () => { console.log(2) }
          ]
          console.info('profile')
          break
      }

      _getData({
        modules: modulesArr,
        url: urlArr,
        id: id,
        wrapper: wrapperArr,
        template: templateObj,
        callback: callbackArr
      })
    })
  }

  function Events () {
    body.on('click', '.js-page', function (e) {
      e.preventDefault()
      if (checkAuth()) {
        _page($(this))
      } else {
        location.href = '/'
      }
    })
  }

  return {
    Events: Events,
    page: _page
  }
}
