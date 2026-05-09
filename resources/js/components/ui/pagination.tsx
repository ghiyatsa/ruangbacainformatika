import * as React from "react"
import { Link, type InertiaLinkProps } from "@inertiajs/react"
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { ChevronLeftIcon, ChevronRightIcon, MoreHorizontalIcon } from "lucide-react"

function Pagination({ className, ...props }: React.ComponentProps<"nav">) {
  return (
    <nav
      role="navigation"
      aria-label="pagination"
      data-slot="pagination"
      className={cn("mx-auto flex w-full justify-center", className)}
      {...props}
    />
  )
}

function PaginationContent({
  className,
  ...props
}: React.ComponentProps<"ul">) {
  return (
    <ul
      data-slot="pagination-content"
      className={cn("flex items-center gap-1", className)}
      {...props}
    />
  )
}

function PaginationItem({ ...props }: React.ComponentProps<"li">) {
  return <li data-slot="pagination-item" {...props} />
}

type PaginationLinkProps = {
  isActive?: boolean
  disabled?: boolean
} & Pick<React.ComponentProps<typeof Button>, "size"> &
  Omit<InertiaLinkProps, "size">

function PaginationLink({
  className,
  isActive,
  disabled,
  size = "icon",
  href,
  // Inertia props to omit when disabled
  as,
  data,
  method,
  only,
  headers,
  preserveScroll,
  preserveState,
  replace,
  queryStringArrayFormat,
  async,
  onBefore,
  onStart,
  onProgress,
  onSuccess,
  onError,
  onCancel,
  onFinish,
  type,
  ...props
}: PaginationLinkProps) {
  const inertiaProps = {
    as,
    data,
    method,
    only,
    headers,
    preserveScroll,
    preserveState,
    replace,
    queryStringArrayFormat,
    async,
    onBefore,
    onStart,
    onProgress,
    onSuccess,
    onError,
    onCancel,
    onFinish,
  }

  return (
    <Button
      asChild
      variant={isActive ? "outline" : "ghost"}
      size={size}
      className={cn(className)}
      disabled={disabled}
    >
      {disabled ? (
        <button
          type="button"
          aria-disabled
          data-slot="pagination-link"
          data-active={isActive}
          {...props}
        />
      ) : (
        <Link
          href={href!}
          aria-current={isActive ? "page" : undefined}
          data-slot="pagination-link"
          data-active={isActive}
          {...inertiaProps}
          {...props}
        />
      )}
    </Button>
  )
}

function PaginationPrevious({
  className,
  text = "Sebelumnya",
  ...props
}: React.ComponentProps<typeof PaginationLink> & { text?: string }) {
  return (
    <PaginationLink
      aria-label="Halaman sebelumnya"
      size="default"
      className={cn("pl-2!", className)}
      {...props}
    >
      <ChevronLeftIcon className="size-4" />
      <span className="hidden sm:block">{text}</span>
    </PaginationLink>
  )
}

function PaginationNext({
  className,
  text = "Berikutnya",
  ...props
}: React.ComponentProps<typeof PaginationLink> & { text?: string }) {
  return (
    <PaginationLink
      aria-label="Halaman berikutnya"
      size="default"
      className={cn("pr-2!", className)}
      {...props}
    >
      <span className="hidden sm:block">{text}</span>
      <ChevronRightIcon className="size-4" />
    </PaginationLink>
  )
}

function PaginationEllipsis({
  className,
  ...props
}: React.ComponentProps<"span">) {
  return (
    <span
      aria-hidden
      data-slot="pagination-ellipsis"
      className={cn(
        "flex size-9 items-center justify-center [&_svg:not([class*='size-'])]:size-4",
        className
      )}
      {...props}
    >
      <MoreHorizontalIcon />
      <span className="sr-only">More pages</span>
    </span>
  )
}

export {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationLink,
  PaginationNext,
  PaginationPrevious,
}
