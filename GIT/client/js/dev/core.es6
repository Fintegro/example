import $ from "jquery";
import { checkAuth } from './modules/coreFunc.es6';

const BODY = $('body');

let auth, app;

require('jquery.easing');

if(!checkAuth()){
	require.ensure([], (require) => {
		auth = require('./_auth.es6');

		auth(BODY);
	});
}else {
	require.ensure([], (require) => {
		app = require('./_app.es6');

		app(BODY);
	});
}