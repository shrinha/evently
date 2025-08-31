import {t} from "@lingui/macro";
import classes from "./FloatingPoweredBy.module.scss";
import classNames from "classnames";
import React from "react";
import {iHavePurchasedALicence, isEvently} from "../../../utilites/helpers.ts";

/**
 * (c) Evently Ltd 2025
 *
 * PLEASE NOTE:
 *
 * Evently is licensed under the GNU Affero General Public License (AGPL) version 3.
 *
 * You can find the full license text at: https://github.com/EventlyDev/Evently/blob/main/LICENCE
 *
 * In accordance with Section 7(b) of the AGPL, you must retain the "Powered by Evently" notice.
 *
 * If you wish to remove this notice, a commercial license is available at: https://Evently/licensing
 */
export const PoweredByFooter = (props: React.DetailedHTMLProps<React.HTMLAttributes<HTMLDivElement>, HTMLDivElement>) => {
    if (iHavePurchasedALicence()) {
        return <></>;
    }

    const footerContent = isEvently() ? (
        <>
            {t`Planning an event?`} {' '}
            <a href="https://Evently?utm_source=app-powered-by-footer&utm_content=try-hi-events-free"
               target="_blank"
               className={classes.ctaLink}
               title={'Effortlessly manage events and sell tickets online with Evently'}>
                {t`Try Evently Free`}
            </a>
        </>
    ) : (
        <>
            {t`Powered by`} {' '}
            <a href="https://Evently?utm_source=app-powered-by-footer"
               target="_blank"
               title={'Effortlessly manage events and sell tickets online with Evently'}>
                Evently
            </a> 🚀
        </>
    );

    return (
        <div {...props} className={classNames(classes.poweredBy, props.className)}>
            <div className={classes.poweredByText}>
                {footerContent}
            </div>
        </div>
    );
}
