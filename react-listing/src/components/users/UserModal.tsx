import { UserModalProps } from '../../types/user';
import { Modal } from '../common/Modal';
import { Avatar } from '../common/Avatar';
import { ContactInfoItem } from './ContactInfoItem';
import { Mail, Phone, Globe, MapPin, Building2 } from 'lucide-react';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';

export const UserModal: React.FC<UserModalProps> = ({
  user,
  isOpen,
  onClose,
}) => {
  if (!user) return null;

  return (
    <Modal isOpen={isOpen} onClose={onClose}>
      <div className="mb-6 text-center">
        <div className="mb-4 flex justify-center">
          <Avatar name={user.name} id={user.id} size="lg" />
        </div>
        <h2 className="text-xl font-bold text-gray-900">{user.name}</h2>
        <p className="mt-1 text-sm text-gray-500">@{user.username}</p>
      </div>

      <div className="space-y-4">
        <ContactInfoItem
          icon={<Mail className="h-5 w-5" />}
          label="Email"
          value={user.email.toLowerCase()}
          href={`mailto:${user.email}`}
          iconBgColor="bg-blue-100"
          iconTextColor="text-blue-600"
        />

        <div className="grid grid-cols-2 gap-3">
          <ContactInfoItem
            icon={<Phone className="h-5 w-5" />}
            label="Phone"
            value={user.phone}
            href={`tel:${user.phone}`}
            iconBgColor="bg-green-100"
            iconTextColor="text-green-600"
          />
          <ContactInfoItem
            icon={<Globe className="h-5 w-5" />}
            label="Website"
            value={user.website}
            href={`https://${user.website}`}
            external
            iconBgColor="bg-purple-100"
            iconTextColor="text-purple-600"
          />
        </div>

        <ContactInfoItem
          icon={<MapPin className="h-5 w-5" />}
          label="Address"
          value={`${user.address.street}, ${user.address.suite}`}
          secondaryValue={`${user.address.city}, ${user.address.zipcode}`}
          iconBgColor="bg-orange-100"
          iconTextColor="text-orange-600"
        />

        <div className="rounded-xl border border-indigo-100 bg-indigo-50 p-4">
          <div className="flex items-center gap-2">
            <Building2 className="h-5 w-5 text-indigo-600" />
            <p className="font-semibold text-indigo-900">{user.company.name}</p>
          </div>
          <p className="mt-2 text-sm italic text-indigo-700">
            "{user.company.catchPhrase}"
          </p>
          <p className="mt-1 text-xs text-indigo-600">
            {user.company.bs}
          </p>
        </div>

        <div className="overflow-hidden rounded-xl">
          <MapContainer
            center={[parseFloat(user.address.geo.lat), parseFloat(user.address.geo.lng)]}
            zoom={4}
            scrollWheelZoom={false}
            style={{ height: '150px', width: '100%' }}
          >
            <TileLayer
              attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <Marker position={[parseFloat(user.address.geo.lat), parseFloat(user.address.geo.lng)]}>
              <Popup>{user.address.city}</Popup>
            </Marker>
          </MapContainer>
        </div>
      </div>
    </Modal>
  );
};
