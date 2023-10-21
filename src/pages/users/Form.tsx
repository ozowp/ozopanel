/**
 * User add and edit form
 * @since 1.0.0
 */

import { FC } from "react";
import { useNavigate } from "react-router-dom";

const Form: FC = () => {

    const navigate = useNavigate();

    const goList= () => {
        navigate('/users');
    };

    return (
        <div className="wam-users-form">
            <h3>Form field</h3>
            <button
                className=''
                onClick={() => goList()}
            >
                Back to User List
            </button>
        </div>
    );
}
export default Form;