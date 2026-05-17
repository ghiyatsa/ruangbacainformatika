import { Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
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

interface DesktopNavProps {
    isActive: (href: string) => boolean;
}

export function DesktopNav({ isActive }: DesktopNavProps) {
    return (
        <NavigationMenu className="hidden min-w-0 md:flex">
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
                                            : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    {item.label}
                                </NavigationMenuTrigger>
                                <NavigationMenuContent>
                                    <ul className="grid w-[400px] gap-3 p-4 md:w-[500px] md:grid-cols-2 lg:w-[600px]">
                                        {item.children.map((child) => (
                                            <ListItem
                                                key={child.href}
                                                title={child.label}
                                                href={child.href}
                                                icon={child.icon}
                                                active={isActive(child.href)}
                                            >
                                                {child.description}
                                            </ListItem>
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
                                        : 'text-muted-foreground hover:text-foreground',
                                )}
                            >
                                <Link href={item.href || '#'}>{item.label}</Link>
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
>(({ className, title, children, active, icon: Icon, ...props }, ref) => {
    return (
        <li>
            <NavigationMenuLink asChild active={active}>
                <Link
                    ref={ref as any}
                    href={props.href!}
                    className={cn(
                        'flex select-none gap-3 rounded-md p-3 leading-none no-underline outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground',
                        active && 'bg-accent/50 text-accent-foreground',
                        className,
                    )}
                >
                    {Icon && (
                        <Icon className="mt-1 h-4 w-4 shrink-0 text-muted-foreground" />
                    )}
                    <div className="space-y-1">
                        <div className="text-sm font-medium leading-none">
                            {title}
                        </div>
                        <p className="line-clamp-2 text-sm leading-snug text-muted-foreground">
                            {children}
                        </p>
                    </div>
                </Link>
            </NavigationMenuLink>
        </li>
    );
});
ListItem.displayName = 'ListItem';
