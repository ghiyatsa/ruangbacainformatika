import type { ComponentProps } from 'react';

export const RuangBacaLogo = (props: ComponentProps<'svg'>) => {
    return (
        <svg
            viewBox="0 0 3400 3400"
            xmlns="http://www.w3.org/2000/svg"
            fillRule="evenodd"
            clipRule="evenodd"
            strokeLinecap="round"
            strokeLinejoin="round"
            {...props}
        >
            {/* Background path from ruangbaca.svg */}
            {/* <path
                d="M3400,1287.283l0,825.433c0,710.471 -576.812,1287.283 -1287.283,1287.283l-825.433,0c-710.471,0 -1287.283,-576.812 -1287.283,-1287.283l0,-825.433c0,-710.471 576.812,-1287.283 1287.283,-1287.283l825.433,0c710.471,0 1287.283,576.812 1287.283,1287.283Z"
                className="fill-primary"
            /> */}
            {/* Logo paths from ruangbaca.svg */}
            <path
                d="M1063.465,2681.729l1273.07,0"
                fill="none"
                className="stroke-foreground"
                strokeWidth="291.36"
            />
            <path
                d="M1063.465,719.271l1273.07,0"
                fill="none"
                className="stroke-foreground"
                strokeWidth="291.36"
            />
            <path
                d="M732.132,1063.465l0,1273.07"
                fill="none"
                className="stroke-foreground"
                strokeWidth="291.36"
            />
            <path
                d="M2666.868,1063.465l0,1273.07"
                fill="none"
                className="stroke-foreground"
                strokeWidth="291.36"
            />
            <path
                d="M1148.294,2220.218l559.706,-520.218l-559.706,-520.218"
                fill="none"
                className="stroke-foreground"
                strokeWidth="180.11"
            />
            <path
                d="M2262.092,2220.218l-497.554,0"
                fill="none"
                className="stroke-foreground"
                strokeWidth="167.5"
            />
            <path
                d="M2086.034,1179.782l0,342.905"
                fill="none"
                className="stroke-foreground"
                strokeOpacity="0.79"
                strokeWidth="96.74"
            />
            <path
                d="M2211.284,1179.782l0,342.905"
                fill="none"
                className="stroke-foreground"
                strokeOpacity="0.51"
                strokeWidth="96.74"
            />
            <path
                d="M1960.783,1179.782l0,342.905"
                fill="none"
                className="stroke-foreground"
                strokeWidth="96.74"
            />
            <path
                d="M2336.535,1179.782l0,342.905"
                fill="none"
                className="stroke-foreground"
                strokeOpacity="0.22"
                strokeWidth="96.74"
            />
        </svg>
    );
};
