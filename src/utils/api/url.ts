/**
 * All register api helper
 * @since 0.1.0
 */
import apiFetch from '@wordpress/api-fetch';

const url = (api: string, from: string) => {
	if (from == 'free') {
		return `/ozopanel/v1/${api}`;
	} else if (from == 'pro') {
		return `/ozopanelp/v1/${api}`;
	}
};

const get = (api: string, args = '', from = 'free') => {
	return apiFetch({
		path: `${url(api, from)}/?${args}`,
	});
};

const getSingle = (api: string, id: number, from = 'free') => {
	return apiFetch({
		path: `${url(api, from)}/${id}`,
	});
};

const add = (api: string, data: object, from = 'free') => {
	return apiFetch({
		path: `${url(api, from)}`,
		method: 'POST',
		data,
	});
};

const edit = (api: string, id: string, data: object, from = 'free') => {
	return apiFetch({
		path: `${url(api, from)}/${id}`,
		method: 'PUT',
		data,
	});
};

const delete = (api: string, id: string, from = 'free') => {
	return apiFetch({
		path: `${url(api, from)}/${id}`,
		method: 'DELETE',
	});
};

export default {
	add,
	get,
	getSingle,
	edit,
	delete,
};
