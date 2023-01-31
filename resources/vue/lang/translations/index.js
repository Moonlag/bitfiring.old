import axios from "axios";
import {SUPPORT_LOCALES} from "../../plugins/i18nPlugin"

const translates = {};
const {translate} = window.common_data.translate

for (let lang of SUPPORT_LOCALES){
    if(!translates.hasOwnProperty(lang)){
        translates[lang] = {}
    }

    if(translate.hasOwnProperty(lang)){
        translates[lang] = {
            messages: translate[lang]
        }
    }else {
        translates[lang] = {
            load: () => axios.post('/api/translate', {lang})
        }
    }

}

export default {
    ...translates
};
