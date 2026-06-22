import { Link } from '@inertiajs/react';
import * as React from 'react';
import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import { cn } from '@/lib/utils';
import { NAV_LINKS } from './constants';
import type { LucideIcon } from 'lucide-react';

interface DesktopNavProps {
    isActive: (href: string) => boolean;
}

export function DesktopNav({ isActive }: DesktopNavProps) {
    return (
        <NavigationMenu className="hidden min-w-0 md:flex" viewport={false}>
            <NavigationMenuList className="gap-0.5 lg:gap-1">
                {NAV_LINKS.map((item) => (
                    <NavigationMenuItem key={item.label}>
                        {item.children ? (
                            <>
                                <NavigationMenuTrigger
                                    className={cn(
                                        'h-10 bg-transparent px-3 text-sm font-medium transition-colors lg:px-4',
                                        item.children.some((child) =>
                                            isActive(child.href),
                                        )
                                            ? 'text-primary'
                                            : 'text-foreground/75 hover:text-foreground',
                                    )}
                                >
                                    {item.label}
                                </NavigationMenuTrigger>
                                <NavigationMenuContent className="left-0">
                                    <ul className="flex w-max min-w-[200px] flex-col gap-1 p-2">
                                        {item.children.map((child) => (
                                            <ListItem
                                                key={child.href}
                                                title={child.label}
                                                href={child.href}
                                                icon={child.icon}
                                                active={isActive(child.href)}
                                            />
                                        ))}
                                    </ul>
                                </NavigationMenuContent>
                            </>
                        ) : (
                            <NavigationMenuLink
                                asChild
                                active={item.href ? isActive(item.href) : false}
                                className={cn(
                                    navigationMenuTriggerStyle(),
                                    'h-10 bg-transparent px-3 text-sm font-medium transition-colors lg:px-4',
                                    item.href && isActive(item.href)
                                        ? 'text-primary'
                                        : 'text-foreground/75 hover:text-foreground',
                                )}
                            >
                                <Link href={item.href || '#'}>
                                    {item.label}
                                </Link>
                            </NavigationMenuLink>
                        )}
                    </NavigationMenuItem>
                ))}
            </NavigationMenuList>
        </NavigationMenu>
    );
}

const ListItem = React.forwardRef<
    React.ElementRef<'a'>,
    React.ComponentPropsWithoutRef<'a'> & {
        active?: boolean;
        icon?: LucideIcon;
    }
>(({ className, title, active, icon: Icon, ...props }, ref) => {
    return (
        <li>
            <NavigationMenuLink asChild active={active}>
                <Link
                    ref={ref as any}
                    href={props.href!}
                    className={cn(
                        'group flex items-center gap-3 rounded-lg p-2.5 leading-none no-underline transition-colors outline-none select-none hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground',
                        active && 'bg-accent/50 text-accent-foreground',
                        className,
                    )}
                >
                    {Icon && (
                        <Icon className="h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-300 group-hover:scale-110" />
                    )}
                    <span className="text-sm leading-none font-medium">
                        {title}
                    </span>
                </Link>
            </NavigationMenuLink>
        </li>
    );
});
ListItem.displayName = 'ListItem';
