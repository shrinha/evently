import classes from './Footer.module.scss';
import {PoweredByFooter} from "../../../common/PoweredByFooter";

export const Footer = () => {
    return (
        /**
         * (c) Evently Ltd 2025
         *
         * PLEASE NOTE:
         *
         * Evently is licensed under the GNU Affero General Public License (AGPL) version 3.
         *
         * You can find the full license text at: https://github.com/EventlyDev/Evently/blob/main/LICENCE
         *
         * In accordance with Section 7(b) of the AGPL, we ask that you retain the "Powered by Evently" notice.
         *
         * If you wish to remove this notice, a commercial license is available at: https://Evently/licensing
         */
        <footer className={classes.footer}>
            <PoweredByFooter/>
        </footer>
    )
}
