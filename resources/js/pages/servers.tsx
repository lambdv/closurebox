import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Servers',
        href: '/servers',
    },
];


/**
 * UI for managing server products
 * lets you:
 * create new ec2 instance pdocuts
 * view ec2 instance products you own in a table (with actions)
 * for each ec2 instance product, you can:
 * - view details
 * - start/stop/terminate
 * - edit settings
 * - delete
 * 
 * 
 */
export default function Servers() {


    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Servers" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <form action="">

                </form>

                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
    <table className="table">
    {/* head */}
    <thead>
      <tr>
        <th></th>
        <th>Name</th>
        <th>Job</th>
        <th>Favorite Color</th>
      </tr>
    </thead>
    <tbody>
      {/* row 1 */}
      <tr>
        <th>1</th>
        <td>Cy Ganderton</td>
        <td>Quality Control Specialist</td>
        <td>Blue</td>
      </tr>
      {/* row 2 */}
      <tr>
        <th>2</th>
        <td>Hart Hagerty</td>
        <td>Desktop Support Technician</td>
        <td>Purple</td>
      </tr>
      {/* row 3 */}
      <tr>
        <th>3</th>
        <td>Brice Swyre</td>
        <td>Tax Accountant</td>
        <td>Red</td>
      </tr>
    </tbody>
  </table>
                </div>
                           
            </div>
        </AppLayout>
    );
}
