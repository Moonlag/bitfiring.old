module.exports = {
	globDirectory: 'public/',
	globPatterns: [
		'**/*.{css,woff2,woff,js,eot,ttf,Identifier,ico}'
	],
	swDest: 'public/sw.js',
	ignoreURLParametersMatching: [
		/^utm_/,
		/^fbclid$/
	]
};