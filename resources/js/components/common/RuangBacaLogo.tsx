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
            {/* Rounded square background using primary color */}
            <rect
                x="0"
                y="0"
                width="3400"
                height="3400"
                rx="1320"
                ry="1320"
                className="fill-primary"
            />
            {/* Top horizontal bar */}
            <path
                d="M1444.743,914.916l1029.862,0"
                fill="none"
                className="stroke-primary-foreground"
                strokeWidth="259.59"
            />
            {/* Bottom horizontal bar */}
            <path
                d="M1444.743,2485.084l1029.862,0"
                fill="none"
                className="stroke-primary-foreground"
                strokeWidth="259.59"
            />
            {/* Right vertical bar */}
            <path
                d="M2727.653,1176.61l0,1046.779"
                fill="none"
                className="stroke-primary-foreground"
                strokeWidth="259.59"
            />
            {/* Middle vertical bar (full opacity) */}
            <path
                d="M1826.056,1355.844l0,688.313"
                fill="none"
                className="stroke-primary-foreground"
                strokeWidth="187.42"
            />
            {/* Middle vertical bar (71% opacity) */}
            <path
                d="M2061.236,1355.844l0,688.313"
                fill="none"
                className="stroke-primary-foreground"
                strokeOpacity="0.71"
                strokeWidth="187.42"
            />
            {/* Middle vertical bar (49% opacity) */}
            <path
                d="M2296.415,1355.844l0,688.313"
                fill="none"
                className="stroke-primary-foreground"
                strokeOpacity="0.49"
                strokeWidth="187.42"
            />
            {/* Left chevron */}
            <path
                d="M672.347,2485.084l772.397,-785.084l-772.397,-785.084"
                fill="none"
                className="stroke-primary-foreground"
                strokeWidth="259.59"
            />
        </svg>
    );
};
