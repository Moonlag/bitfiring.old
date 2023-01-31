import axios from "axios";

import walletModule from "./wallet";
import transactionModule from "./transaction";
import bonusesModule from "./bonuses";
import freespinsModule from "./freespins";

export default {
    wallet: walletModule(axios),
    transaction: transactionModule(axios),
    bonuses: bonusesModule(axios),
    freespins: freespinsModule(axios)
}
