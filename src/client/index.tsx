import './styles.css';
import { definePterodactylExtension } from '@pterodactyl/sdk';
import EggSwitcher from './EggSwitcher';

export default definePterodactylExtension({
    setup({ screens, slots }) {
        screens.register('groups', () => import('./screens/GroupsScreen'));
        slots.register('server.startup.form', EggSwitcher);
    },
});
