import {t} from '@lingui/macro';
import {IconCalendarPlus} from '@tabler/icons-react';
import {GenericErrorPage} from "../../../common/GenericErrorPage";
import {isEvently} from "../../../../utilites/helpers.ts";

export const EventNotAvailable = () => {
    return (
        <GenericErrorPage
            title={t`Event Not Available`}
            description={t`The event you're looking for is not available at the moment. It may have been removed, expired, or the URL might be incorrect.`}
            pageTitle={t`Event Not Available`}
            metaDescription={t`The event you're looking for is not available at the moment. It may have been removed, expired, or the URL might be incorrect.`}
            buttonText={isEvently() ? t`Create your own event` : undefined}
            buttonUrl={isEvently() ? "https://app.Evently/auth/register?utm_source=app.Evently&utm_content=organizer-not-found/create-event" : undefined}
            buttonIcon={<IconCalendarPlus size={18}/>}
        />
    );
};

export default EventNotAvailable;
