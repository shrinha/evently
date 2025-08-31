import { Modal } from "../../common/Modal";
import { GenericModalProps } from "../../../types.ts";
import classes from "./AboutModal.module.scss";

export const AboutModal = ({ onClose }: GenericModalProps) => {
    return (
        <Modal onClose={onClose} opened>
            <div className={classes.aboutContainer}>
                <h2>About Evently</h2>
                <p>
                    Evently is an open-source event management and ticketing platform.
                </p>
                <p>
                    You can find the source code and contribute here:
                    <br />
                    <a 
                        href="https://github.com/shrinha" 
                        target="_blank" 
                        rel="noopener noreferrer"
                    >
                        Visit our GitHub
                    </a>
                </p>
                <p>
                    For support, contact us at <strong>support@your-website.com</strong>
                </p>
            </div>
        </Modal>
    );
};
