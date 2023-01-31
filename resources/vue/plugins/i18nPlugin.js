import { nextTick } from 'vue'
import { createI18n } from 'vue-i18n'

export const SUPPORT_LOCALES = ['en', 'ru', 'ph', 'fr', 'in', 'id', 'it', 'jp', 'kr', 'my', 'sa', 'br', 'th', 'tr', 'vn', 'cn']

export const defaultLanguage = 'en';

export function setupI18n(options = { locale: 'en' }) {
    const i18n = createI18n(options)
    setI18nLanguage(i18n, options.locale)
    return i18n
}

export function setI18nLanguage(i18n, locale) {
    if (i18n.mode === 'legacy') {
        i18n.global.locale = locale
    } else {
        i18n.global.locale.value = locale
    }
    /**
     * NOTE:
     * If you need to specify the language setting for headers, such as the `fetch` API, set it here.
     * The following is an example for axios.
     *
     * axios.defaults.headers.common['Accept-Language'] = locale
     */
    document.querySelector('html').setAttribute('lang', locale)
}

export async function loadLocaleMessages(i18n, locale) {
    // load locale messages with dynamic import
    const {data:messages} = await axios.post('/api/translate', {locale})
    // set locale and locale message
    i18n.global.setLocaleMessage(locale, messages)

    return nextTick()
}
