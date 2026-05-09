import { cn } from "@/lib/utils"
import { AutoSkeleton } from 'auto-skeleton-react'

function Skeleton({
  className,
  ...props
}: React.ComponentProps<"div">) {
  return (
    <AutoSkeleton 
        loading={true} 
        config={{ 
            animation: 'pulse',
        }}
    >
      <div
        data-slot="skeleton"
        className={cn("bg-muted/50 rounded-md dark:bg-muted/20", className)}
        {...props}
      />
    </AutoSkeleton>
  )
}

export { Skeleton }
